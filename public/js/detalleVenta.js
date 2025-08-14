const createDetalleVentaPanel = () => {
    const ventaStore = Ext.getStore('ventaStore');
    const productoFisicoStore = Ext.getStore('productoFisicoStore');
    const productoDigitalStore = Ext.getStore('productoDigitalStore');
    if (!ventaStore || !productoFisicoStore || !productoDigitalStore) {
        throw new Error('Faltan stores requeridos');
    }

    Ext.define('App.model.DetalleVenta', {
        extend: "Ext.data.Model",
        fields: [
            { name: 'idVenta', type: 'int' },
            { name: 'lineNumber', type: 'int' },
            { name: 'idProducto', type: 'int' },
            { name: 'cantidad', type: 'int' },
            { name: 'precioUnitario', type: 'float' },
            { name: 'subtotal', type: 'float' },
            {
                name: 'nombreProducto',
                type: 'string',
                convert: function (value, record) {
                    const idProducto = record.get('idProducto');
                    if (!idProducto) return '';
                    let producto = productoFisicoStore.findRecord('id', idProducto);
                    if (producto) return producto.get('nombre');
                    producto = productoDigitalStore.findRecord('id', idProducto);
                    if (producto) return producto.get('nombre');
                    return 'Producto no encontrado';
                }
            }
        ]
    });

    let detalleVentaStore = Ext.create('Ext.data.Store', {
        storeId: 'detalleVentaStore',
        model: 'App.model.DetalleVenta',
        proxy: {
            type: 'rest',
            url: 'api/detalleVenta.php',
            reader: { type: 'json', rootProperty: '' },
            writer: { type: 'json', rootProperty: '', writeAllFields: true },
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        // Campos del formulario (sin lineNumber en alta)
        const formItems = [
            {
                xtype: "combobox",
                fieldLabel: "Venta",
                name: "idVenta",
                store: ventaStore,
                queryMode: "local",
                displayField: "id",
                valueField: "id",
                forceSelection: true,
                allowBlank: false,
                editable: false
            },
            {
                xtype: "combobox",
                fieldLabel: "Producto",
                name: "idProducto",
                store: Ext.create('Ext.data.Store', {
                    fields: ['id', 'nombre'],
                    data: [
                        ...productoFisicoStore.getRange().map(r => ({
                            id: r.get('id'),
                            nombre: r.get('nombre')
                        })),
                        ...productoDigitalStore.getRange().map(r => ({
                            id: r.get('id'),
                            nombre: r.get('nombre')
                        }))
                    ]
                }),
                queryMode: "local",
                displayField: "nombre",
                valueField: "id",
                forceSelection: true,
                allowBlank: false,
                editable: false
            },
            { xtype: "numberfield", fieldLabel: "Cantidad", name: "cantidad", allowBlank: false, minValue: 1 }
        ];

        // Solo en edición, muestra el lineNumber (readonly)
        if (!isNew) {
            formItems.splice(1, 0, {
                xtype: "numberfield",
                fieldLabel: "Line Number",
                name: "lineNumber",
                readOnly: true
            });
        }

        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nuevo Detalle Venta" : "Editar Detalle Venta",
            modal: true,
            width: 400,
            layout: "fit",
            items: [
                {
                    xtype: "form",
                    bodyPadding: 12,
                    defaults: { anchor: "100%" },
                    items: formItems
                }
            ],
            buttons: [
                {
                    text: "Guardar",
                    handler() {
                        const form = this.up("window").down("form").getForm();
                        if (!form.isValid()) return;
                        const values = form.getValues();
                        rec.set('idVenta', parseInt(values.idVenta));
                        rec.set('idProducto', parseInt(values.idProducto));
                        rec.set('cantidad', parseInt(values.cantidad));
                        // Asigna lineNumber solo en alta (deja que el backend lo asigne)
                        if (!isNew) {
                            rec.set('lineNumber', parseInt(values.lineNumber));
                        }
                        if (isNew) detalleVentaStore.add(rec);
                        detalleVentaStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Detalle de venta guardado correctamente.");
                                detalleVentaStore.reload();
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
                                if (isNew) detalleVentaStore.remove(rec);
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
        title: "Detalle Venta",
        store: detalleVentaStore,
        itemId: "detalleVentaPanel",
        layout: "fit",
        columns: [
            { text: "ID Venta", width: 100, dataIndex: "idVenta" },
            { text: "Line Number", width: 100, dataIndex: "lineNumber" },
            { text: "Producto", flex: 1, dataIndex: "nombreProducto" },
            { text: "Cantidad", width: 100, dataIndex: "cantidad" },
            { text: "Precio Unitario", width: 120, dataIndex: "precioUnitario" },
            { text: "Subtotal", width: 120, dataIndex: "subtotal" }
        ],
        tbar: [
            {
                text: 'Agregar',
                handler: () => openDialog(Ext.create("App.model.DetalleVenta"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#detalleVentaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un detalle de venta");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#detalleVentaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione un detalle de venta");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar este detalle de venta?", (btn) => {
                        if (btn === "yes") {
                            detalleVentaStore.remove(sel);
                            detalleVentaStore.sync({
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