const createPersonaJuridicaPanel = () => {
    if (!Ext.ClassManager.isCreated("App.model.PersonaJuridica")) {
        Ext.define('App.model.PersonaJuridica', {
            extend: "Ext.data.Model",
            fields: [
                {name:'id', type:'int'},
                {name:'direccion', type:'string'},
                {name:'email', type:'string'},
                {name:'telefono', type:'string'},
                {name:'razonSocial', type:'string'},
                {name:'ruc', type:'string'},
                {name:'representanteLegal', type:'string'}
            ]
        });
    }

    let personaJuridicaStore = Ext.create('Ext.data.Store', {
        storeId: 'personaJuridicaStore',
        model: 'App.model.PersonaJuridica',
        proxy: {
            type: 'rest',
            url: 'api/personaJuridica.php',
            reader: {type:'json', rootProperty:''},
            writer: {type:'json', rootProperty:'', writeAllFields:true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nueva Persona Jurídica" : "Editar Persona Jurídica",
            modal: true,
            width: 500,
            layout: "fit",
            items: [
                {
                    xtype: "form",
                    bodyPadding: 12,
                    defaults: { anchor: "100%" },
                    items: [
                        { xtype: "hiddenfield", name: "id" },
                        { xtype: "textfield", fieldLabel: "Razón Social", name: "razonSocial", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Representante Legal", name: "representanteLegal", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "RUC", name: "ruc", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Email", name: "email", vtype: "email" },
                        { xtype: "textfield", fieldLabel: "Teléfono", name: "telefono" },
                        { xtype: "textfield", fieldLabel: "Dirección", name: "direccion" }
                    ]
                }
            ],
            buttons: [
                {
                    text: "Guardar",
                    handler() {
                        const form = this.up("window").down("form").getForm();
                        if (!form.isValid()) return;
                        form.updateRecord(rec);
                        if (isNew) personaJuridicaStore.add(rec);
                        personaJuridicaStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Persona Jurídica guardada correctamente.");
                                personaJuridicaStore.reload();
                                this.up("window").close();
                            },
                            failure: () => {
                                Ext.Msg.alert("Error", "No se pudo guardar.");
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
        title: "Personas Jurídicas",
        store: personaJuridicaStore,
        itemId: "personaJuridicaPanel",
        layout: "fit",
        columns: [
            { text: "ID", width: 40, sortable: false, hideable: false, dataIndex: "id" },
            { text: "Razón Social", flex: 1, sortable: false, hideable: false, dataIndex: "razonSocial" },
            { text: "Representante Legal", flex: 1, sortable: false, hideable: false, dataIndex: "representanteLegal" },
            { text: "RUC", flex: 1, sortable: false, hideable: false, dataIndex: "ruc" },
            { text: "Email", flex: 1, sortable: false, hideable: false, dataIndex: "email" },
            { text: "Teléfono", flex: 1, sortable: false, hideable: false, dataIndex: "telefono" },
            { text: "Dirección", flex: 1, sortable: false, hideable: false, dataIndex: "direccion" }
        ],
        tbar: [
            {
                text: "Agregar",
                handler: () => openDialog(Ext.create("App.model.PersonaJuridica"), true)
            },
            {
                text: "Actualizar",
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#personaJuridicaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una persona jurídica");
                    openDialog(sel, false);
                }
            },
            {
                text: "Eliminar",
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#personaJuridicaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una persona jurídica");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar esta persona jurídica?", (btn) => {
                        if (btn === "yes") {
                            personaJuridicaStore.remove(sel);
                            personaJuridicaStore.sync({
                                success: () => Ext.Msg.alert("Éxito", "Eliminado"),
                                failure: () => Ext.Msg.alert("Error", "No se pudo eliminar"),
                            });
                        }
                    });
                }
            }
        ]
    });
    return grid;
}