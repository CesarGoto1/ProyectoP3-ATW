Ext.onReady(()=>{
    const personaJuridicaPanel = createPersonaJuridicaPanel();
    const personaNaturalPanel = createPersonaNaturalPanel();
    const categoriaPanel = createCategoriaPanel();
    const productoFisicoPanel = createProductoFisicoPanel();
    const productoDigitalPanel = createProductoDigitalPanel();
    const ventaPanel = createVentaPanel();
    const detalleVentaPanel = createDetalleVentaPanel();
    const facturaPanel = createFacturaPanel();
    const usuarioPanel = createUsuarioPanel();
    const rolPanel = createRolPanel();
    const permisoPanel = createPermisoPanel();
    const rolPermisoPanel = createRolPermisoPanel();
    const mainCard = Ext.create('Ext.panel.Panel',{
        region: 'center',
        layout: 'card',
        items: [personaJuridicaPanel, 
            personaNaturalPanel, 
            categoriaPanel,
            productoFisicoPanel,
            productoDigitalPanel,
            ventaPanel,
            detalleVentaPanel,
            facturaPanel,
            usuarioPanel,
            rolPanel,
            permisoPanel,
            rolPermisoPanel
        ]
    });

    Ext.create("Ext.container.Viewport",{
        id: 'mainViewPort',
        layout: 'border',
        items: [
        {
            region: 'north',
            xtype: 'toolbar',
            items:[
            {
                text: 'Persona Juridica',
                handler: () => mainCard.getLayout().setActiveItem(personaJuridicaPanel)
            },
            {
                text: 'Persona Natural',
                handler: () => mainCard.getLayout().setActiveItem(personaNaturalPanel)
            },
            {
                text: 'Categoría',
                handler: () => mainCard.getLayout().setActiveItem(categoriaPanel)
            },
            {
                text: 'Producto Físico',
                handler: () => mainCard.getLayout().setActiveItem(productoFisicoPanel)
            },
            {
                text: 'Producto Digital',
                handler: () => mainCard.getLayout().setActiveItem(productoDigitalPanel)
            },
            {
                text: 'Venta',
                handler: () => mainCard.getLayout().setActiveItem(ventaPanel)
            },
            {
                text: 'Detalle Venta',
                handler: () => mainCard.getLayout().setActiveItem(detalleVentaPanel)
            },
            {
                text: 'Factura',
                handler: () => mainCard.getLayout().setActiveItem(facturaPanel)
            },
            {
                text: 'Usuario',
                handler: () => mainCard.getLayout().setActiveItem(usuarioPanel)
            },
            {
                text: 'Rol',
                handler: () => mainCard.getLayout().setActiveItem(rolPanel)
            },
            {
                text: 'Permiso',
                handler: () => mainCard.getLayout().setActiveItem(permisoPanel)
            },
            {
                text: 'Rol Permiso',
                handler: () => mainCard.getLayout().setActiveItem(rolPermisoPanel)
            }
            ]
        },
        mainCard],
    });
})