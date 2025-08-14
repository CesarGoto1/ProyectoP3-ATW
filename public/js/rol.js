const createRolPanel = () => {
    Ext.define('App.model.Rol', {
        extend: "Ext.data.Model",
        fields: [
            {name: 'id', type: 'int'},
            {name: 'nombre', type: 'string'}
        ]
    });

    let rolStore = Ext.create('Ext.data.Store', {
        storeId: 'rolStore',
        model: 'App.model.Rol',
        proxy: {
            type: 'rest',
            url: 'api/rol.php',
            reader: {type: 'json', rootProperty: ''},
            writer: {type: 'json', rootProperty: '', writeAllFields: true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nuevo Rol" : "Editar Rol",
            modal: true,
            width: 350,
            layout: "fit",
            items: [
                {
                    xtype: "form",
                    bodyPadding: 12,
                    defaults: { anchor: "100%" },
                    items: [
                        { xtype: "hiddenfield", name: "id" },
                        { xtype: "textfield", fieldLabel: "Nombre", name: "nombre", allowBlank: false }
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
                        rec.set('nombre', values.nombre);

                        if (isNew) rolStore.add(rec);
                        rolStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Rol guardado correctamente.");
                                rolStore.reload();
                                this.up("window").close();
                            },
                            failure: (batch, options) => {
                                let msg = "No se pudo guardar.";
                                try {
                                    const response = batch.operations[0].getError().response;
                                    if (response && response.responseText) {
                                        const json = JSON.parse(response.responseText);
                                        if (json.error) msg = json.error;
                                    }
                                } catch (e) {}
                                if (isNew) rolStore.remove(rec);
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
        win.down("form").loadRecord(rec);
        win.show();
    };

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Roles",
        store: rolStore,
        itemId: "rolPanel",
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
                text: "Nombre",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "nombre"
            }
        ],
        tbar: [
            {
                text: 'Agregar',
                handler: () => openDialog(Ext.create("App.model.Rol"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#rolPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un rol");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#rolPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un rol");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar este rol?", (btn) => {
                        if (btn === "yes") {
                            rolStore.remove(sel);
                            rolStore.sync({
                                success: () => Ext.Msg.alert("Éxito", "Eliminado"),
                                failure: (batch, options) => {
                                    let msg = "No se pudo eliminar";
                                    try {
                                        const response = batch.operations[0].getError().response;
                                        if (response && response.responseText) {
                                            const json = JSON.parse(response.responseText);
                                            if (json.error) msg = json.error;
                                        }
                                    } catch (e) {}
                                    rolStore.rejectChanges();
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