const createRolPermisoPanel = () => {
    // Stores de roles y permisos
    const rolStore = Ext.getStore('rolStore');
    const permisoStore = Ext.getStore('permisoStore');
    if (!rolStore || !permisoStore) {
        throw new Error('Faltan stores de rol o permiso');
    }

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

    const openDialog = () => {
        const win = Ext.create("Ext.window.Window", {
            title: "Agregar Permiso a Rol",
            modal: true,
            width: 400,
            layout: "fit",
            items: [
                {
                    xtype: "form",
                    bodyPadding: 12,
                    defaults: { anchor: "100%" },
                    items: [
                        {
                            xtype: "combobox",
                            fieldLabel: "Rol",
                            name: "idRol",
                            store: rolStore,
                            queryMode: "local",
                            displayField: "nombre",
                            valueField: "id",
                            forceSelection: true,
                            allowBlank: false,
                            editable: false
                        },
                        {
                            xtype: "combobox",
                            fieldLabel: "Permiso",
                            name: "idPermiso",
                            store: permisoStore,
                            queryMode: "local",
                            displayField: "codigo",
                            valueField: "id",
                            forceSelection: true,
                            allowBlank: false,
                            editable: false
                        }
                    ]
                }
            ],
            buttons: [
                {
                    text: "Guardar",
                    handler() {
                        const form = this.up("window").down("form").getForm();
                        if (!form.isValid()) return;
                        const values = form.getValues();
                        // El backend espera idRol y idPermiso
                        Ext.Ajax.request({
                            url: 'api/rolPermiso.php',
                            method: 'POST',
                            jsonData: {
                                idRol: parseInt(values.idRol),
                                idPermiso: parseInt(values.idPermiso)
                            },
                            success: function() {
                                Ext.Msg.alert("Éxito", "Permiso asignado al rol correctamente.");
                                rolPermisoStore.reload();
                                win.close();
                            },
                            failure: function(response) {
                                let msg = "No se pudo asignar el permiso.";
                                try {
                                    const json = Ext.decode(response.responseText);
                                    if (json.error) msg = json.error;
                                } catch (e) {}
                                Ext.Msg.alert("Error", msg);
                            }
                        });
                    }
                },
                {
                    text: "Cancelar",
                    handler() {
                        this.up("window").close();
                    }
                }
            ]
        });
        win.show();
    };

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Rol-Permiso",
        store: rolPermisoStore,
        itemId: "rolPermisoPanel",
        layout: "fit",
        columns: [
            { text: "ID Rol", width: 60, dataIndex: "rolId" },
            { text: "Nombre Rol", flex: 1, dataIndex: "rolNombre" },
            { text: "ID Permiso", width: 80, dataIndex: "permisoId" },
            { text: "Código Permiso", flex: 1, dataIndex: "permisoCodigo" }
        ],
        tbar: [
            {
                text: 'Agregar',
                handler: openDialog
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#rolPermisoPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una relación Rol-Permiso");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar esta relación?", (btn) => {
                        if (btn === "yes") {
                            Ext.Ajax.request({
                                url: 'api/rolPermiso.php',
                                method: 'DELETE',
                                jsonData: {
                                    idRol: sel.get('rolId'),
                                    idPermiso: sel.get('permisoId')
                                },
                                success: function() {
                                    Ext.Msg.alert("Éxito", "Relación eliminada.");
                                    rolPermisoStore.reload();
                                },
                                failure: function(response) {
                                    let msg = "No se pudo eliminar";
                                    try {
                                        const json = Ext.decode(response.responseText);
                                        if (json.error) msg = json.error;
                                    } catch (e) {}
                                    Ext.Msg.alert("Error", msg);
                                }
                            });
                        }
                    });
                }
            }
        ]
    });
    return grid;
}