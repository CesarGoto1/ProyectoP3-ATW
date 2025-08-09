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
                    return c.SubCategoria ? `${c.Categoria} - ${c.SubCategoria}` : c.Categoria;
                },
            },
            {name: 'descripcion', type: 'string'},
            {name: 'precioUnitario', type: 'float'},
            {name: 'urlDescarga', type: 'string'},
            {name: 'licencia', type: 'string'},
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


    grid = Ext.create("Ext.grid.Panel",{
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
                dataIndex: "peso",
            },
            {
                text: "Licencia",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "alto",
            }
        ],
        tbar:[
            {text: 'Add'},
            {text: 'Update'},
            {text: 'Delete'},
            {text: 'Find by Id'}        
        ]
    });
    return grid;
}