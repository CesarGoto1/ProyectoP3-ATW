const createVentaPanel = () => {
    const personaJuridicaStore = Ext.getStore('personaJuridicaStore');
    const personaNaturalStore = Ext.getStore('personaNaturalStore');
    let errores = [];
    if (!personaJuridicaStore) {
        errores.push('personaJuridicaStore no encontrado. Cargue el archivo correspondiente.');
    }
    if (!personaNaturalStore) {
        errores.push('personaNaturalStore no encontrado. Cargue el archivo correspondiente.');
    }
    if (errores.length > 0) {
        throw new Error(errores.join(' | '));
    }

    Ext.define('App.model.Venta', {
        extend: "Ext.data.Model",
        fields: [
            {name:'id', type:'int'},
            {name:'fecha', type:'date'},
            {name:'clienteNombre', type:'string', convert: function(value, record){
                const cliente = record.get('cliente');
                if (cliente && cliente.tipoCliente === 'App\\Entities\\PersonaJuridica') {
                    return cliente.razonSocial;
                } else if (cliente && cliente.tipoCliente === 'App\\Entities\\PersonaNatural') {
                    return `${cliente.nombres} ${cliente.apellidos}`;
                }
                return '';
            }},
            {name:'total', type:'float'},
            {name:'estado', type:'string'}
        ]
    });

    let ventaStore = Ext.create('Ext.data.Store', {
        storeId: 'ventaStore',
        model: 'App.model.Venta',
        proxy: {
            type: 'rest',
            url: 'api/venta.php',
            reader: {type:'json', rootProperty:''},
            writer: {type:'json', rootProperty:'', writeAllFields:true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Ventas",
        store: ventaStore,
        itemId: "ventaPanel",
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
                text: "Fecha",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "fecha",
                renderer: Ext.util.Format.dateRenderer('Y-m-d')
            },
            {
                text: "Cliente",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "clienteNombre"
            },
            {
                text: "Total",
                flex: 0.5,
                sortable: false,
                hideable: false,
                dataIndex: "total",
                renderer: Ext.util.Format.usMoney
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
            {text: 'Add'},
            {text: 'Update'},
            {text: 'Delete'}
        ]
    });
    return grid;
}