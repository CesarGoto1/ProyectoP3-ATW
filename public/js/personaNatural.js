const createPersonaNaturalPanel = () => {
    if (!Ext.ClassManager.isCreated("App.model.PersonaNatural")) {
        Ext.define('App.model.PersonaNatural', {
            extend: "Ext.data.Model",
            fields: [
                {name:'id', type:'int'},
                {name:'direccion', type:'string'},
                {name:'email', type:'string'},
                {name:'telefono', type:'string'},
                {name:'nombres', type:'string'},
                {name:'apellidos', type:'string'},
                {name:'cedula', type:'string'}
            ]
        });
    }

    let personaNaturalStore = Ext.create('Ext.data.Store', {
        storeId: 'personaNaturalStore',
        model: 'App.model.PersonaNatural',
        proxy: {
            type: 'rest',
            url: 'api/personaNatural.php',
            reader: {type:'json', rootProperty:''},
            writer: {type:'json', rootProperty:'', writeAllFields:true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nueva Persona Natural" : "Editar Persona Natural",
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
                        { xtype: "textfield", fieldLabel: "Nombres", name: "nombres", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Apellidos", name: "apellidos", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Cédula", name: "cedula", allowBlank: false },
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
                            if (isNew) personaNaturalStore.add(rec);
                            personaNaturalStore.sync({
                                success: () => {
                                    Ext.Msg.alert("Éxito", "Persona Natural guardada correctamente.");
                                    personaNaturalStore.reload();
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
                                    
                                    if (isNew) personaNaturalStore.remove(rec);
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
        title: "Personas Naturales",
        store: personaNaturalStore,
        itemId: "personaNaturalPanel",
        layout: "fit",
        columns: [
            { text: "ID", width: 40, sortable: false, hideable: false, dataIndex: "id" },
            { text: "Nombres", flex: 1, sortable: false, hideable: false, dataIndex: "nombres" },
            { text: "Apellidos", flex: 1, sortable: false, hideable: false, dataIndex: "apellidos" },
            { text: "Cédula", flex: 1, sortable: false, hideable: false, dataIndex: "cedula" },
            { text: "Email", flex: 1, sortable: false, hideable: false, dataIndex: "email" },
            { text: "Teléfono", flex: 1, sortable: false, hideable: false, dataIndex: "telefono" },
            { text: "Dirección", flex: 1, sortable: false, hideable: false, dataIndex: "direccion" }
        ],
        tbar: [
            {
                text: "Agregar",
                handler: () => openDialog(Ext.create("App.model.PersonaNatural"), true)
            },
            {
                text: "Actualizar",
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#personaNaturalPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una persona natural");
                    openDialog(sel, false);
                }
            },
            {
                text: "Eliminar",
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#personaNaturalPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una persona natural");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar esta persona natural?", (btn) => {
                        if (btn === "yes") {
                            personaNaturalStore.remove(sel);
                            personaNaturalStore.sync({
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