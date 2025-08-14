const createCategoriaPanel = () => {
    Ext.define('App.model.Categoria', {
        extend: "Ext.data.Model",
        fields: [
            {name:'id', type:'int'},
            {name:'nombre', type:'string'},
            {name:'descripcion', type:'string'},
            {name:'estado', type:'string'},
            {name:'idPadre', type:'int', allowNull: true},
            {
                name: 'nombrePadre',
                type: 'string',
                convert: function(value, record) {
                    const idPadre = record.get('idPadre');
                    if (idPadre === null || idPadre === undefined) return 'Sin padre';
                    // Busca el nombre del padre en el store global
                    if (categoriaStore) {
                        const padre = categoriaStore.findRecord('id', idPadre, 0, false, true, true);
                        return padre ? padre.get('nombre') : 'Sin padre';
                    }
                    return 'Sin padre';
                }
            }
        ]
    });

    let categoriaStore = Ext.create('Ext.data.Store', {
        storeId: 'categoriaStore',
        model: 'App.model.Categoria',
        proxy: {
            type: 'rest',
            url: 'api/categoria.php',
            reader: {type:'json', rootProperty:''},
            writer: {type:'json', rootProperty:'', writeAllFields:true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });
    categoriaStore.on('load', function(store) {
        store.each(function(rec) {
            const idPadre = rec.get('idPadre');
            if (idPadre === null || idPadre === undefined) {
                rec.set('nombrePadre', 'Sin padre');
            } else {
                const padre = store.findRecord('id', idPadre, 0, false, true, true);
                rec.set('nombrePadre', padre ? padre.get('nombre') : 'Sin padre');
            }
            rec.commit(); 
        });
    });
    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nueva Categoría" : "Editar Categoría",
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
                        { xtype: "textfield", fieldLabel: "Nombre", name: "nombre", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Descripción", name: "descripcion" },
                        {
                            xtype: "combobox",
                            fieldLabel: "Estado",
                            name: "estado",
                            store: ["activo", "inactivo"],
                            forceSelection: true,
                            editable: false,
                            allowBlank: false
                        },
                        {
                            xtype: "combobox",
                            fieldLabel: "Categoría Padre",
                            name: "idPadre",
                            store: {
                                xtype: 'store',
                                fields: ['id', 'nombre'],
                            },
                            queryMode: "local",
                            displayField: "nombre",
                            valueField: "id",
                            allowBlank: true,
                            emptyText: "Sin padre",
                            editable: false,
                        }
                    ]
                }
            ],
            buttons: [
                {
                    text: "Guardar",
                    handler() {
                        const form = this.up("window").down("form").getForm();
                        const values = form.getValues();
                        if (values.id && values.idPadre && parseInt(values.id) === parseInt(values.idPadre)) {
                            Ext.Msg.alert("Error", "Una categoría no puede ser su propio padre.");
                            return;
                        }
                        if (!form.isValid()) return;
                        let idPadre = values.idPadre;
                        if (idPadre === "" || idPadre === undefined || idPadre === null) {
                            idPadre = null;
                        } else {
                            idPadre = parseInt(idPadre);
                            if (isNaN(idPadre)) idPadre = null;
                        }
                        rec.set('idPadre', idPadre);

                        form.updateRecord(rec);

                        if (isNew) categoriaStore.add(rec);
                        categoriaStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Categoría guardada correctamente.");
                                categoriaStore.reload();
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
                                if (isNew) categoriaStore.remove(rec);
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
            const form = win.down("form");
            const combo = form.down('combobox[name=idPadre]');
            const data = categoriaStore.getRange()
                .filter(r => !rec.get('id') || r.get('id') !== rec.get('id'))
                .map(r => ({ id: r.get('id'), nombre: r.get('nombre') }));
            data.unshift({ id: null, nombre: 'Sin padre' });
            combo.getStore().loadData(data);

            if (!rec.get('idPadre') || rec.get('idPadre') === null) {
                combo.setValue(null);
            } else {
                combo.setValue(rec.get('idPadre'));
            }
            win.show();
    };

    const grid = Ext.create("Ext.grid.Panel", {
        title: "Categorías",
        store: categoriaStore,
        itemId: "categoriaPanel",
        layout: "fit",
        columns: [
            { text: "ID", width: 40, sortable: false, hideable: false, dataIndex: "id" },
            { text: "Nombre", flex: 1, sortable: false, hideable: false, dataIndex: "nombre" },
            { text: "Descripción", flex: 1, sortable: false, hideable: false, dataIndex: "descripcion" },
            { text: "Estado", flex: 0.5, sortable: false, hideable: false, dataIndex: "estado" },
            { text: "Categoria Padre", width: 150, sortable: false, hideable: false, dataIndex: "nombrePadre" }
        ],
        tbar: [
            {
                text: "Agregar",
                handler: () => openDialog(Ext.create("App.model.Categoria"), true)
            },
            {
                text: "Actualizar",
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#categoriaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una categoría");
                    openDialog(sel, false);
                }
            },
            {
                text: "Eliminar",
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#categoriaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una categoría");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar esta categoría?", (btn) => {
                        if (btn === "yes") {
                            categoriaStore.remove(sel);
                            categoriaStore.sync({
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