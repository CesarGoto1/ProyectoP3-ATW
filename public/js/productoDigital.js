const createProductoDigitalPanel = () => {
    const categoriaStore = Ext.getStore('categoriaStore');
    if(!categoriaStore){
        throw new Error('categoriaStore no encontrado. Cargue el archivo categoria.js')
    }

    Ext.define('App.model.ProductoDigital', {
        extend: "Ext.data.Model",
        fields: [
            {name: 'id', type: 'int'},
            {name: 'nombre', type: 'string'},
            {
                name: 'categoriaNombre',
                convert: (v, rec) => {
                    const c = rec.get('categoria');
                    if (!c) return "";
                    return c.SubCategoria && c.SubCategoria !== null && c.SubCategoria !== ""
                        ? c.SubCategoria
                        : c.Categoria;
                },
            },
            {name: 'descripcion', type: 'string'},
            {name: 'precioUnitario', type: 'float'},
            {name: 'stock', type: 'int'},
            {name: 'urlDescarga', type: 'string'},
            {name: 'licencia', type: 'string'},
            {name: 'idCategoria', mapping: 'categoria.id', type: 'int'}
        ],
    });

    const productoDigitalStore = Ext.create("Ext.data.Store",{
        storeId: 'productoDigitalStore',
        model: 'App.model.ProductoDigital',
        proxy:{
            type:           'rest', 
            url:            'api/productoDigital.php',
            reader:         {type:'json', rootProperty:''},
            writer:         {type:'json', rootProperty:'', writeAllFields:true},
            appendId:       false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nuevo Producto Digital" : "Editar Producto Digital",
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
                        { xtype: "textfield", fieldLabel: "Nombre", name: "nombre", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Descripción", name: "descripcion" },
                        { xtype: "numberfield", fieldLabel: "Precio Unitario", name: "precioUnitario", allowBlank: false, minValue: 0 },
                        { xtype: "numberfield", fieldLabel: "Stock", name: "stock", allowBlank: false, minValue: 0 },
                        { xtype: "textfield", fieldLabel: "URL de Descarga", name: "urlDescarga", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Licencia", name: "licencia", allowBlank: false },
                        {
                            xtype: "combobox",
                            fieldLabel: "Categoría",
                            name: "idCategoria",
                            store: categoriaStore,
                            queryMode: "local",
                            displayField: "nombre",
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
                        form.updateRecord(rec);
                        rec.set('idCategoria', parseInt(form.findField('idCategoria').getValue()));
                        if (isNew) productoDigitalStore.add(rec);
                        productoDigitalStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Producto Digital guardado correctamente.");
                                productoDigitalStore.reload();
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
                                if (isNew) productoDigitalStore.remove(rec);
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

    const grid = Ext.create("Ext.grid.Panel",{
        title: "Productos Digitales",
        store: productoDigitalStore,
        itemId: "productoDigitalPanel",
        layout: "fit",
        columns: [
            {
                text: "ID",
                width: 40,
                sortable: false,
                hideable: false,
                dataIndex: "id",
            },
            {
                text: "Nombre",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "nombre",
            },
            {
                text: "Descripción",
                flex: 2,
                sortable: false,
                hideable: false,
                dataIndex: "descripcion",
            },
            {
                text: "Categoría",
                flex: 2,
                sortable: false,
                hideable: false,
                dataIndex: "categoriaNombre",
            },
            {
                text: "Precio Unitario",
                width: 150,
                sortable: false,
                hideable: false,
                dataIndex: "precioUnitario",
            },
            {
                text: "Stock",
                width: 80,
                sortable: false,
                hideable: false,
                dataIndex: "stock",
            },
            {
                text: "URL de Descarga",
                flex: 2,
                sortable: false,
                hideable: false,
                dataIndex: "urlDescarga",
            },
            {
                text: "Licencia",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "licencia",
            }
        ],
        tbar:[
            {
                text: 'Agregar',
                handler: () => openDialog(Ext.create("App.model.ProductoDigital"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#productoDigitalPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un producto digital");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#productoDigitalPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un producto digital");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar este producto digital?", (btn) => {
                        if (btn === "yes") {
                            productoDigitalStore.remove(sel);
                            productoDigitalStore.sync({
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