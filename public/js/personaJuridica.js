const createPersonaJuridicaPanel = () =>{
    Ext.define('App.model.PersonaJuridica',{
        extend: "Ext.data.Model",
        fields: [
            {name:'id',                         type:'int'},
            {name:'direccion',                  type:'string'},
            {name:'email',                      type:'string'},
            {name:'telefono',                   type:'string'},
            {name:'razonSocial',                type:'string'},
            {name:'ruc',                        type:'string'},
            {name:'representanteLegal',         type:'string'}
        ]
    });

    let personaJuridicaStore = Ext.create('Ext.data.Store',{
        storeId: 'personaJuridicaStore',
        model: 'App.model.PersonaJuridica',
        proxy:{
            type:           'rest', 
            url:            'api/personaJuridica.php',
            reader:         {type:'json', rootProperty:''},
            writer:         {type:'json', rootProperty:'', writeAllFields:true},
            appendId:       false
        },
        autoLoad: true,
        autoSync: false
    });

    const grid = Ext.create("Ext.grid.Panel",{
        title: "Personas Juridicas",
        store: personaJuridicaStore,
        itemId: "personaJuridicaPanel",
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
                text: "Razon Social",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "razonSocial"
            },
            {
                text: "Representante Legal",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "representanteLegal"
            },
            {
                text: "RUC",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "ruc"
            },
            {
                text: "Email",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "email"
            },
            {
                text: "Telefono",
                flex: 1,
                sortable: false,
                hideable: false,
                dataIndex: "telefono"
            },
            {
                text: "Direccion",
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