const createProductoFisicoPanel = () => {
    const categoriaStore = Ext.getStore('categoriaStore');
    if(!categoriaStore){
        throw new Error('categoriaStore no encontrado. Cargue el archivo categoria.js')
    }

    Ext.define('App.model.ProductoFisico',{
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
            {name: 'peso', type: 'float'},
            {name: 'alto', type: 'float'},
            {name: 'ancho', type: 'float'},
            {name: 'profundidad', type: 'float'},
            {name: 'idCategoria', mapping: 'categoria.id', type: 'int'}
        ],
    });

    const productoFisicoStore = Ext.create("Ext.data.Store",{
        storeId: 'productoFisicoStore',
        model: 'App.model.ProductoFisico',
        proxy:{
            type: 'rest', 
            url: 'api/productoFisico.php',
            reader: {type:'json', rootProperty:''},
            writer: {type:'json', rootProperty:'', writeAllFields:true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nuevo Producto Físico" : "Editar Producto Físico",
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
                        { xtype: "numberfield", fieldLabel: "Peso", name: "peso", allowBlank: false, minValue: 0 },
                        { xtype: "numberfield", fieldLabel: "Alto", name: "alto", allowBlank: false, minValue: 0 },
                        { xtype: "numberfield", fieldLabel: "Ancho", name: "ancho", allowBlank: false, minValue: 0 },
                        { xtype: "numberfield", fieldLabel: "Profundidad", name: "profundidad", allowBlank: false, minValue: 0 },
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
                        // Asegura que idCategoria sea int
                        rec.set('idCategoria', parseInt(form.findField('idCategoria').getValue()));
                        if (isNew) productoFisicoStore.add(rec);
                        productoFisicoStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Producto Físico guardado correctamente.");
                                productoFisicoStore.reload();
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
                                if (isNew) productoFisicoStore.remove(rec);
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
        title: "Productos Físicos",
        store: productoFisicoStore,
        itemId: "productosFisicosPanel",
        layout: "fit",
        columns: [
            { text: "ID", width: 40, dataIndex: "id" },
            { text: "Nombre", flex: 1, dataIndex: "nombre" },
            { text: "Descripción", flex: 2, dataIndex: "descripcion" },
            { text: "Categoría", flex: 2, dataIndex: "categoriaNombre" },
            { text: "Precio Unitario", width: 150, dataIndex: "precioUnitario" },
            { text: "Stock", width: 80, dataIndex: "stock" },
            { text: "Peso", width: 100, dataIndex: "peso" },
            { text: "Alto", width: 100, dataIndex: "alto" },
            { text: "Ancho", width: 100, dataIndex: "ancho" },
            { text: "Profundidad", width: 100, dataIndex: "profundidad" }
        ],
        tbar:[
            {
                text: 'Agregar',
                handler: () => openDialog(Ext.create("App.model.ProductoFisico"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#productosFisicosPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un producto físico");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#productosFisicosPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un producto físico");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar este producto físico?", (btn) => {
                        if (btn === "yes") {
                            productoFisicoStore.remove(sel);
                            productoFisicoStore.sync({
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