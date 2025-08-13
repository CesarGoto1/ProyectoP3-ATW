const createRolPermisoPanel = () => {
    Ext.define('App.model.RolPermiso', {
        extend: "Ext.data.Model",
        fields: [
            {name: 'rolId', mapping: 'rol.id', type: 'int'},
            {name: 'rolNombre', mapping: 'rol.nombre', type: 'string'},
            {name: 'permisoId', mapping: 'permiso.id', type: 'int'},
            {name: 'permisoCodigo', mapping: 'permiso.codigo', type: 'string'}
        ]
    });

    let rolPermisoStore = Ext.create('Ext.data.Store', {
        storeId: 'rolPermisoStore',
        model: 'App.model.RolPermiso',
        proxy: {
            type: 'rest',
            url: 'api/rolPermiso.php',
            reader: {type: 'json', rootProperty: ''},
            writer: {type: 'json', rootProperty: '', writeAllFields: true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Rol-Permiso",
        store: rolPermisoStore,
        itemId: "rolPermisoPanel",
        layout: "fit",
        columns: [
            {
                text: "ID Rol",
                width: 60,
                dataIndex: "rolId"
            },
            {
                text: "Nombre Rol",
                flex: 1,
                dataIndex: "rolNombre"
            },
            {
                text: "ID Permiso",
                width: 80,
                dataIndex: "permisoId"
            },
            {
                text: "Código Permiso",
                flex: 1,
                dataIndex: "permisoCodigo"
            }
        ],
        tbar: [
            {text: 'Agregar'},
            {text: 'Eliminar'}
        ]
    });
    return grid;
}