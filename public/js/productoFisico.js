const createProductoFisicoPanel = () => {
    const categoriaStore = Ext.getStore('categoriaStore');
    if(!categoriaStore){
        throw new Error('categoriaStore no encontrado. Cargue el archivo categoria.js')
    }

    Ext.define('App.model.ProductoFisico',{
        extend: "Ext.data.Model",
        fields: [
            {name: 'id',                                    type: 'int'},
            {name: 'nombre',                                type: 'string'},
            {name: 'categoriaNombre',       convert:(v,rec)=>{
                const c = rec.get('categoria');
                return c ? `${c.Categoria} - ${c.SubCategoria} ` :"";
            },
            },
            {name: 'descripcion',                           type: 'string'},
            {name: 'precioUnitario',                        type: 'float'},
            {name: 'stock',                                 type: 'int'},
            {name: 'peso',                                  type: 'float'},
            {name: 'alto',                                  type: 'float'},
            {name: 'ancho',                                 type: 'float'},
            {name: 'profundidad',                           type: 'float'},
        ],
    });

    const productoFisicoStore = Ext.create("Ext.data.Store",{
        storeId: 'productoFisicoStore',
        model: 'App.model.ProductoFisico',
        proxy:{
            type:           'rest', 
            url:            'api/productoFisico.php',
            reader:         {type:'json', rootProperty:''},
            writer:         {type:'json', rootProperty:'', writeAllFields:true},
            appendId:       false
        },
        autoLoad: true,
        autoSync: false
    });


    grid = Ext.create("Ext.grid.Panel",{
        title: "Productos Físicos",
        store: productoFisicoStore,
        itemId: "productosFisicosPanel",
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
                text: "Peso",
                width: 100,
                sortable: false,
                hideable: false,
                dataIndex: "peso",
            },
            {
                text: "Alto",
                width: 100,
                sortable: false,
                hideable: false,
                dataIndex: "alto",
            },
            {
                text: "Ancho",
                width: 100,
                sortable: false,
                hideable: false,
                dataIndex: "ancho",
            },
            {
                text: "Profundidad",
                width: 100,
                sortable: false,
                hideable: false,
                dataIndex: "profundidad",
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