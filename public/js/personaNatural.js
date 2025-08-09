const createPersonaNaturalPanel = () =>{
    Ext.define('App.model.PersonaNatural',{
        extend: "Ext.data.Model",
        fields: [
            {name:'id',                         type:'int'},
            {name:'direccion',                  type:'string'},
            {name:'email',                      type:'string'},
            {name:'telefono',                   type:'string'},
            {name:'nombres',                    type:'string'},
            {name:'apellidos',                  type:'string'},
            {name:'cedula',                     type:'string'}
        ]
    });

    let personaNaturalStore = Ext.create('Ext.data.Store',{
        storeId: 'personaNaturalStore',
        model: 'App.model.PersonaNatural',
        proxy:{
            type:           'rest', 
            url:            'api/personaNatural.php',
            reader:         {type:'json', rootProperty:''},
            writer:         {type:'json', rootProperty:'', writeAllFields:true},
            appendId:       false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel",{
        title: "Personas Naturales",
        store: personaNaturalStore,
        itemId: "personaNaturalPanel",
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
                text: "Nombres",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "nombres"
            },
            {
                text: "Apellidos",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "apellidos"
            },
            {
                text: "Cédula",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "cedula"
            },
            {
                text: "Email",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "email"
            },
            {
                text: "Teléfono",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "telefono"
            },
            {
                text: "Dirección",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "direccion"
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