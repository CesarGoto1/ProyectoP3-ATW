const createFacturaPanel = () => {
    const ventaStore = Ext.getStore('ventaStore');
    if (!ventaStore) {
        throw new Error('ventaStore no encontrado. Cargue el archivo venta.js');
    }

    Ext.define('App.model.Factura', {
        extend: "Ext.data.Model",
        fields: [
            {name: 'id', type: 'int'},
            {name: 'idVenta', type: 'int'},
            {name: 'numero', type: 'string'},
            {name: 'claveAcceso', type: 'string'},
            {name: 'fechaEmision', type: 'date'},
            {name: 'estado', type: 'string'},
            {
                name: 'ventaInfo',
                convert: function(value, record) {
                    const venta = record.get('venta');
                    if (!venta) return '';
                    return `Venta #${venta.id} - ${venta.fecha} - $${venta.total}`;
                }
            }
        ]
    });

    let facturaStore = Ext.create('Ext.data.Store', {
        storeId: 'facturaStore',
        model: 'App.model.Factura',
        proxy: {
            type: 'rest',
            url: 'api/factura.php',
            reader: {type: 'json', rootProperty: ''},
            writer: {type: 'json', rootProperty: '', writeAllFields: true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Facturas",
        store: facturaStore,
        itemId: "facturaPanel",
        layout: "fit",
        columns: [
            {
                text: "ID",
                width: 40,
                sortable: false,
                hideable: false,
                dataIndex: "id"
            },
            {
                text: "Venta",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "ventaInfo"
            },
            {
                text: "Número",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "numero"
            },
            {
                text: "Clave Acceso",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "claveAcceso"
            },
            {
                text: "Fecha Emisión",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "fechaEmision",
                renderer: Ext.util.Format.dateRenderer('Y-m-d')
            },
            {
                text: "Estado",
                flex: 0.5,
                sortable: false,
                hideable: false,
                dataIndex: "estado"
            }
        ],
        tbar: [
            {text: 'Agregar'},
            {text: 'Actualizar'},
            {text: 'Eliminar'},
            {text: 'Buscar por Id'}
        ]
    });
    return grid;
}