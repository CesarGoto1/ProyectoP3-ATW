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

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nuevo Permiso" : "Editar Permiso",
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
                        { xtype: "textfield", fieldLabel: "Código", name: "codigo", allowBlank: false }
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
                        rec.set('codigo', values.codigo);

                        if (isNew) permisoStore.add(rec);
                        permisoStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Permiso guardado correctamente.");
                                permisoStore.reload();
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
                                if (isNew) permisoStore.remove(rec);
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
            {
                text: 'Agregar',
                handler: () => openDialog(Ext.create("App.model.Permiso"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#permisoPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un permiso");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#permisoPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un permiso");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar este permiso?", (btn) => {
                        if (btn === "yes") {
                            permisoStore.remove(sel);
                            permisoStore.sync({
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
                                    permisoStore.rejectChanges();
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