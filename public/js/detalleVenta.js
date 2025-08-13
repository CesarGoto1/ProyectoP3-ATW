const createDetalleVentaPanel = () => {
    const ventaStore = Ext.getStore('ventaStore');
    if (!ventaStore) {
        throw new Error('ventaStore no encontrado. Cargue el archivo venta.js');
    }

    const productoDigitalStore = Ext.getStore('productoDigitalStore');
    if (!productoDigitalStore) {
        throw new Error('productoDigitalStore no encontrado. Cargue el archivo productoDigital.js');
    }

    const productoFisicoStore = Ext.getStore('productoFisicoStore');
    if (!productoFisicoStore) {
        throw new Error('productoFisicoStore no encontrado. Cargue el archivo productoFisico.js');
    }

    Ext.define('App.model.DetalleVenta',{
        extend: "Ext.data.Model",
        fields: [
            {name: 'idVenta', type: 'int'},
            {name: 'lineNumber', type: 'int'},
            {name: 'idProducto', type: 'int'},
            {name: 'cantidad', type: 'int'},
            {name: 'precioUnitario', type: 'float'},
            {name: 'subtotal', type: 'float'},
            {
                name: 'nombreProducto',
                type: 'string',
                convert: function(value, record){
                    const idProducto = record.get('idProducto');
                    if (!idProducto) return '';

                    let producto = productoDigitalStore.findRecord('id', idProducto);
                    if (producto) {
                        return producto.get('nombre');
                    }

                    producto = productoFisicoStore.findRecord('id', idProducto);
                    if (producto) {
                        return producto.get('nombre');
                    }

                    return 'Producto no encontrado';
                }
            }
        ],
    });

    let detalleVentaStore = Ext.create('Ext.data.Store',{
        storeId: 'detalleVentaStore',
        model: 'App.model.DetalleVenta',
        proxy:{
            type:           'rest', 
            url:            'api/detalleVenta.php',
            reader:         {type:'json', rootProperty:''},
            writer:         {type:'json', rootProperty:'', writeAllFields:true},
            appendId:       false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel",{
        title: "Detalle Venta",
        store: detalleVentaStore,
        itemId: "detalleVentaPanel",
        layout: "fit",
        columns:[
            {
                text: "ID Venta",
                width: 100,
                sortable: true,
                hideable: false,
                dataIndex: "idVenta"
            },
            {
                text: "Line Number",
                width: 100,
                sortable: true,
                hideable: false,
                dataIndex: "lineNumber"
            },
            {
                text: "Producto",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "nombreProducto"
            },
            {
                text: "Cantidad",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "cantidad"
            },
            {
                text: "Precio Unitario",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "precioUnitario"
            },
            {
                text: "Subtotal",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "subtotal"
            },
        ],
    tbar: [
            {text: 'Add'},
            {text: 'Update'},
            {text: 'Delete'}
        ]
    });
    return grid;
}