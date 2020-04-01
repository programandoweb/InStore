import Config from "./config";

/*SE DEBE PASAR EL EVENT*/
const InputTypeFilter=(event)=>{
  console.log(event.target.type);
  switch (event.target.type) {
    case "tel":
      let phoneno = /^\d{10}$/;
      if((event.target.value.match(phoneno))){
        return event.target.value
      }else{
        return "error"
      }
    break;
    default:
  }
}

const CutString=(text,wordsToCut)=>{
    // if (wordsToCut==undefined) {
    //   wordsToCut = 20;
    // }
    // var wordsArray = text.split(" ");
    // if(wordsArray.length>wordsToCut){
    //     var strShort = "";
    //     for(i = 0; i < wordsToCut; i++){
    //         strShort += wordsArray[i] + " ";
    //     }
    //     return strShort+"...";
    // }else{
    //     return text;
    // }
};

const FechaHoy = ()  =>{
  /*FECHA DE HOY*/
  let date    =   new Date( );
  let day     =   date.getDate();
      if (day < 10) {
        day = "0"+day;
      }
  let month  =  date.getUTCMonth();
      if (month < 10) {
        month  =  month+1;
        month  =  "0"+month;
      }else {
        month  =  month+1;
      }

  let year   =  date.getUTCFullYear();
  let newDate = year+"-"+month+"-"+day;
  return newDate;
}

const Convertir_base64 = (result)  =>{
  // return new Promise(resolve => {
  //   let base64;
  //   base64 =  FileSystem.readAsStringAsync(  result.uri,{encoding: FileSystem.EncodingType.Base64,});
  //   resolve(base64)
  // });
}


const Get = (user,methods,component,id) =>{
  // methods.setState({loading:true})
  // var me      =   user;
  // var headers =   new Headers();
  // var data    =   new FormData();
  //     data.append ("u", me.token);
  //     data.append ("token", me.token);
  // let cabecera  =   {
  //                     headers:headers,
  //                     method: "POST",
  //                     body: data
  //                   }
  //
  // fetch(Config.ApiRest + "get?modulo=Profesores&m=getInfo&component="+component+"&id="+id+"&formato=json&u="+me.token,cabecera)
  //   .then(response => response.json())
  //   .then(data => { procesarGet(methods,component,id,data)})
  //   .catch((error) => { console.log(error)  });
}

const PostAsync =   (modulo,m,objeto) =>{
  console.log(objeto);
}


const Post    =   (modulo,m,methods,objeto) =>{

  var me      =   methods.state.user;
  var headers =   new Headers();
  var data    =   new FormData();
      Object.entries(objeto).map((v,k) => {
        data.append (v[0],v[1]);
      })
      data.append ("u", me.token);
      data.append ("token", me.token);

  let cabecera  =   {
                      headers:headers,
                      method: "POST",
                      body: data
                    }

  fetch(Config.ApiRest + "get?modulo="+modulo+"&m="+m+"&formato=json&u="+me.token,cabecera)
    .then(response => response.json())
    .then(data =>     { console.log(data) })
    .catch((error) => { console.log(error)  });
}


export default {  CutString,
                  FechaHoy,
                  Convertir_base64,
                  Get,
                  Post,
                  InputTypeFilter
                }
