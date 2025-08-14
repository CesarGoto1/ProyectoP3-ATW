const createVentaPanel = () => {
    const personaJuridicaStore = Ext.getStore('personaJuridicaStore');
    const personaNaturalStore = Ext.getStore('personaNaturalStore');
    let errores = [];
    if (!personaJuridicaStore) {
        errores.push('personaJuridicaStore no encontrado. Cargue el archivo correspondiente.');
    }
    if (!personaNaturalStore) {
        errores.push('personaNaturalStore no encontrado. Cargue el archivo correspondiente.');
    }
    if (errores.length > 0) {
        throw new Error(errores.join(' | '));
    }

    Ext.define('App.model.Venta', {
        extend: "Ext.data.Model",
        fields: [
            {name:'id', type:'int'},
            {name:'fecha', type:'date'},
            {name:'clienteNombre', type:'string', convert: function(value, record){
                const cliente = record.get('cliente');
                if (cliente && cliente.tipoCliente === 'App\\Entities\\PersonaJuridica') {
                    return cliente.razonSocial;
                } else if (cliente && cliente.tipoCliente === 'App\\Entities\\PersonaNatural') {
                    return `${cliente.nombres} ${cliente.apellidos}`;
                }
                return '';
            }},
            {name:'total', type:'float'},
            {name:'estado', type:'string'},
            {name:'idCliente', type:'int'}
        ]
    });

    let ventaStore = Ext.create('Ext.data.Store', {
        storeId: 'ventaStore',
        model: 'App.model.Venta',
        proxy: {
            type: 'rest',
            url: 'api/venta.php',
            reader: {type:'json', rootProperty:''},
            writer: {type:'json', rootProperty:'', writeAllFields:true},
            appendId: false
        },
        autoLoad: true,
        autoSync: false
    });

    const openDialog = (rec, isNew) => {
        const win = Ext.create("Ext.window.Window", {
            title: isNew ? "Nueva Venta" : "Editar Venta",
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
                        { xtype: "datefield", fieldLabel: "Fecha", name: "fecha", type: "date",allowBlank: false, format: "Y-m-d" },
                        {
                            xtype: "combobox",
                            fieldLabel: "Cliente",
                            name: "idCliente",
                            store: Ext.create('Ext.data.Store', {
                                fields: ['id', 'nombre'],
                                data: [
                                    ...personaNaturalStore.getRange().map(r => ({
                                        id: r.get('id'),
                                        nombre: `${r.get('nombres')} ${r.get('apellidos')}`
                                    })),
                                    ...personaJuridicaStore.getRange().map(r => ({
                                        id: r.get('id'),
                                        nombre: r.get('razonSocial')
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
                        { xtype: "numberfield", fieldLabel: "Total", name: "total", allowBlank: false, minValue: 0 },
                        {
                            xtype: "combobox",
                            fieldLabel: "Estado",
                            name: "estado",
                            store: ["borrador", "emitida", "anulada"],
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

    // Formatea la fecha a "Y-m-d" antes de setear en el record
    let fecha = values.fecha;

if (fecha instanceof Date) {
    fecha = Ext.Date.format(fecha, "Y-m-d");
} else if (/^\d+$/.test(fecha)) {
    // Si es número (timestamp en ms o s)
    if (fecha.length === 10) { // segundos
        fecha = Ext.Date.format(new Date(parseInt(fecha, 10) * 1000), "Y-m-d");
    } else { // milisegundos
        fecha = Ext.Date.format(new Date(parseInt(fecha, 10)), "Y-m-d");
    }
}
rec.set('fecha', fecha);
    rec.set('idCliente', parseInt(values.idCliente));
    rec.set('total', parseFloat(values.total));
    rec.set('estado', values.estado);

    if (isNew) ventaStore.add(rec);
    ventaStore.sync({
        success: () => {
            Ext.Msg.alert("Éxito", "Venta guardada correctamente.");
            ventaStore.reload();
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
            if (isNew) ventaStore.remove(rec);
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
        title: "Ventas",
        store: ventaStore,
        itemId: "ventaPanel",
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
                text: "Fecha",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "fecha",
                renderer: Ext.util.Format.dateRenderer('Y-m-d')
            },
            {
                text: "Cliente",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "clienteNombre"
            },
            {
                text: "Total",
                flex: 0.5,
                sortable: false,
                hideable: false,
                dataIndex: "total",
                renderer: Ext.util.Format.usMoney
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
                handler: () => openDialog(Ext.create("App.model.Venta"), true)
            },
            {
                text: 'Actualizar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#ventaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una venta");
                    openDialog(sel, false);
                }
            },
            {
                text: 'Eliminar',
                handler: () => {
                    const grid = Ext.ComponentQuery.query("#ventaPanel")[0];
                    const sel = grid.getSelection()[0];
                    if (!sel) return Ext.Msg.alert("Advertencia", "Seleccione una venta");
                    Ext.Msg.confirm("Confirmar", "¿Eliminar esta venta?", (btn) => {
                        if (btn === "yes") {
                            ventaStore.remove(sel);
                            ventaStore.sync({
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