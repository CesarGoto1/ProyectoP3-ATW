const createPermisoPanel = () => {
    Ext.define('App.model.Permiso', {
        extend: "Ext.data.Model",
        fields: [
            {name: 'id', type: 'int'},
            {name: 'codigo', type: 'string'}
        ]
    });

    let permisoStore = Ext.create('Ext.data.Store', {
        storeId: 'permisoStore',
        model: 'App.model.Permiso',
        proxy: {
            type: 'rest',
            url: 'api/permiso.php',
            reader: {type: 'json', rootProperty: ''},
            writer: {type: 'json', rootProperty: '', writeAllFields: true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Permisos",
        store: permisoStore,
        itemId: "permisoPanel",
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
                text: "Código",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "codigo"
            }
        ],
        tbar: [
            {text: 'Add'},
            {text: 'Update'},
            {text: 'Delete'},
        ]
    });
    return grid;
}