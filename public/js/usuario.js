const createUsuarioPanel = () => {
    Ext.define('App.model.Usuario', {
        extend: "Ext.data.Model",
        fields: [
            {name: 'id', type: 'int'},
            {name: 'username', type: 'string'},
            {name: 'estado', type: 'string'}
        ]
    });

    let usuarioStore = Ext.create('Ext.data.Store', {
        storeId: 'usuarioStore',
        model: 'App.model.Usuario',
        proxy: {
            type: 'rest',
            url: 'api/usuario.php',
            reader: {type: 'json', rootProperty: ''},
            writer: {type: 'json', rootProperty: '', writeAllFields: true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Usuarios",
        store: usuarioStore,
        itemId: "usuarioPanel",
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
                text: "Usuario",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "username"
            },
            {
                text: "Estado",
                flex: 1,
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