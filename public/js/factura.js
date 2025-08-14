const createFacturaPanel = () => {
    const ventaStore = Ext.getStore('ventaStore');
    if (!ventaStore) {
        throw new Error('ventaStore no encontrado. Cargue el archivo venta.js');
    }

    Ext.define('App.model.Factura', {
        extend: "Ext.data.Model",
        fields: [
            {name: 'id', type: 'int'},
            {name: 'idVenta', type: 'int'},
            {name: 'numero', type: 'string'},
            {name: 'claveAcceso', type: 'string'},
            {name: 'fechaEmision', type: 'date'},
            {name: 'estado', type: 'string'},
            {
                name: 'ventaInfo',
                convert: function(value, record) {
                    const venta = record.get('venta');
                    if (!venta) return '';
                    return `Venta #${venta.id} - ${venta.fecha} - $${venta.total}`;
                }
            }
        ]
    });

    let facturaStore = Ext.create('Ext.data.Store', {
        storeId: 'facturaStore',
        model: 'App.model.Factura',
        proxy: {
            type: 'rest',
            url: 'api/factura.php',
            reader: {type: 'json', rootProperty: ''},
            writer: {type: 'json', rootProperty: '', writeAllFields: true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nueva Factura" : "Editar Factura",
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
                        { xtype: "textfield", fieldLabel: "Número", name: "numero", allowBlank: false },
                        { xtype: "textfield", fieldLabel: "Clave Acceso", name: "claveAcceso", allowBlank: false },
                        { xtype: "datefield", fieldLabel: "Fecha Emisión", name: "fechaEmision", allowBlank: false, format: "Y-m-d" },
                        {
                            xtype: "combobox",
                            fieldLabel: "Estado",
                            name: "estado",
                            store: ["pendiente", "emitida"],
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
                        // Formatea la fecha antes de guardar
                        let fechaEmision = values.fechaEmision;
                        if (fechaEmision instanceof Date) {
                            fechaEmision = Ext.Date.format(fechaEmision, "Y-m-d");
                        } else if (/^\d+$/.test(fechaEmision)) {
                            if (fechaEmision.length === 10) {
                                fechaEmision = Ext.Date.format(new Date(parseInt(fechaEmision, 10) * 1000), "Y-m-d");
                            } else {
                                fechaEmision = Ext.Date.format(new Date(parseInt(fechaEmision, 10)), "Y-m-d");
                            }
                        }
                        rec.set('idVenta', parseInt(values.idVenta));
                        rec.set('numero', values.numero);
                        rec.set('claveAcceso', values.claveAcceso);
                        rec.set('fechaEmision', fechaEmision);
                        rec.set('estado', values.estado);

                        if (isNew) facturaStore.add(rec);
                        facturaStore.sync({
                            success: () => {
                                Ext.Msg.alert("Éxito", "Factura guardada correctamente.");
                                facturaStore.reload();
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
                                if (isNew) facturaStore.remove(rec);
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
        title: "Facturas",
        store: facturaStore,
        itemId: "facturaPanel",
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
                text: "Venta",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "ventaInfo"
            },
            {
                text: "Número",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "numero"
            },
            {
                text: "Clave Acceso",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "claveAcceso"
            },
            {
                text: "Fecha Emisión",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "fechaEmision",
                renderer: Ext.util.Format.dateRenderer('Y-m-d')
            },
            {
                text: "Estado",
                flex: 0.5,
                sortable: false,
                hideable: false,
                dataIndex: "estado"
            }
        ],
        tbar: [
            {
                text: 'Agregar',
                handler: () => openDialog(Ext.create("App.model.Factura"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#facturaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una factura");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#facturaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una factura");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar esta factura?", (btn) => {
                        if (btn === "yes") {
                            facturaStore.remove(sel);
                            facturaStore.sync({
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
                                    // Revertir el registro eliminado si hubo error
                                    facturaStore.rejectChanges();
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