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

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nuevo Usuario" : "Editar Usuario",
            modal: true,
            width: 400,
            layout: "fit",
            items: [
                {
                    xtype: "form",
                    bodyPadding: 12,
                    defaults: { anchor: "100%" },
                    items: [
                        { xtype: "hiddenfield", name: "id" },
                        { xtype: "textfield", fieldLabel: "Usuario", name: "username", allowBlank: false },
                        {
    xtype: "textfield",
    fieldLabel: "Contraseña",
    name: "password",
    inputType: "password",
    allowBlank: isNew,
    triggers: {
        toggle: {
            // Usa el trigger nativo, sin iconos personalizados ni CSS
            handler: function(field) {
                const dom = field.inputEl.dom;
                if (dom.type === "password") {
                    dom.type = "text";
                    field.setFieldLabel("Contraseña (visible)");
                } else {
                    dom.type = "password";
                    field.setFieldLabel("Contraseña");
                }
            },
            tooltip: 'Mostrar/Ocultar'
        }
    }
},
                        {
                            xtype: "combobox",
                            fieldLabel: "Estado",
                            name: "estado",
                            store: ["activo", "inactivo"],
                            forceSelection: true,
                            editable: false,
                            allowBlank: false
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
                        rec.set('username', values.username);
                        rec.set('estado', values.estado);
                        // Solo envía password si es nuevo o si se cambió
                        if (values.password) rec.set('password', values.password);

                        if (isNew) usuarioStore.add(rec);
                        usuarioStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Usuario guardado correctamente.");
                                usuarioStore.reload();
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
                                if (isNew) usuarioStore.remove(rec);
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
            {
                text: 'Agregar',
                handler: () => openDialog(Ext.create("App.model.Usuario"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#usuarioPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un usuario");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#usuarioPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un usuario");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar este usuario?", (btn) => {
                        if (btn === "yes") {
                            usuarioStore.remove(sel);
                            usuarioStore.sync({
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
                                    usuarioStore.rejectChanges();
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