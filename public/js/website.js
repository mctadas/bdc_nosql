markdownSettings = {
    nameSpace:           "textile",
    previewParserPath:   "/admin/admin/preview-markup",
    onShiftEnter:        {keepDefault:false, replaceWith:'\n\n'},
    markupSet: [
        {name:'Antraštė 1', key:'1', openWith:'h1(!(([![Class]!]))!). ', placeHolder:'Antraštė...' },
        {name:'Antraštė 2', key:'2', openWith:'h2(!(([![Class]!]))!). ', placeHolder:'Antraštė...' },
        {name:'Antraštė 3', key:'3', openWith:'h3(!(([![Class]!]))!). ', placeHolder:'Antraštė...' },
        {name:'Antraštė 4', key:'4', openWith:'h4(!(([![Class]!]))!). ', placeHolder:'Antraštė...' },
        {name:'Antraštė 5', key:'5', openWith:'h5(!(([![Class]!]))!). ', placeHolder:'Antraštė...' },
        {name:'Antraštė 6', key:'6', openWith:'h6(!(([![Class]!]))!). ', placeHolder:'Antraštė...' },
        {name:'Paragrafas', key:'P', openWith:'p(!(([![Class]!]))!). '}, 
        {separator:'---------------' },
        {name:'Pastorinti', key:'B', closeWith:'*', openWith:'*'}, 
        {name:'Paversti', key:'I', closeWith:'_', openWith:'_'}, 
        {name:'Pabraukti', key:'S', closeWith:'-', openWith:'-'}, 
        {separator:'---------------' },
        {name:'Sąrašas su ženkleliais', openWith:'(!(* |!|*)!)'}, 
        {name:'Sąrašas su numeriais', openWith:'(!(# |!|#)!)'}, 
        {separator:'---------------' },
        {name:'Paveikslėlis', replaceWith:'![![URL:!:http://]!]([![Alternatyvus aprašymas]!])!'}, 
        {name:'Nuoroda', openWith:'"', closeWith:'([![Pavadinimas]!])":[![Nuoroda:!:http://]!]', placeHolder:'Nuorodos tekstas' },
        {separator:'---------------' },
        {name:'Kabutės', openWith:'bq(!(([![Class]!]))!). '}, 
        {name:'Fragmentas', openWith:'@', closeWith:'@'}, 
        {separator:'---------------' },       
        {name:'Peržiūra', call:'preview', className:'preview'}
    ]
}
