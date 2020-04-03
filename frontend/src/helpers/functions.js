import Config from "./config";
import Store from "./store";

/*SE DEBE PASAR EL EVENT*/
const InputTypeFilter=(event)=>{
  switch (event.target.type) {
    case "tel":
      let phoneno = /^\d{10}$/;
      if((event.target.value.match(phoneno)) || 1===1){
        ///console.log(event.target.type);
        return event.target.value
      }else{
        return {error:"error"}
      }
    break;
    case "email":
      return event.target.value
    break;
    default:
    return event.target.value
  }
}

const CutString=(text,wordsToCut)=>{
    // if (wordsToCut===undefined) {
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


const Get = (modulo,m,objeto) =>{
  let me        =   (Store.get("user")!=null)?Store.get("user"):{token:"PGRW_REGISTER"};
  let headers   =   new Headers();
  let data      =   new FormData();

  Object.entries(objeto).map((v,k) => {
    data.append (v[0],v[1]);
  })

  data.append ("u", me.token);
  data.append ("PUBLIC_KEY", Config.PUBLIC_KEY);

  let cabecera  =   {
                      headers:headers,
                      method: "POST",
                      body: data
                    }

  fetch(Config.ConfigApirest + "get?modulo="+modulo+"&m="+m+"&formato=json&u="+me.token,cabecera)
      .then(response  =>  response.json()        )
      .then(data      =>  { console.log(data)   })
      .catch((error)  =>  { console.log(error)  });
}

const PostAsync =  async (modulo,m,objeto,context) =>{
  let me        =   (Store.get("user")!=null)?Store.get("user"):{token:"PGRW_REGISTER"};
  let headers   =   new Headers();
  let data      =   new FormData();

  Object.entries(objeto).map((v,k) => {
    data.append (v[0],v[1]);
  })

  data.append ("u", me.token);
  data.append ("token", me.token);

  data.append ("PUBLIC_KEY", Config.PUBLIC_KEY);

  if (Store.get("security")===null) {
    data.append ("PRIVATE_KEY", Config.PRIVATE_KEY);
  }else {
    data.append ("tokens_access",Store.get("security"));
  }

  let cabecera  =   {
                      headers:headers,
                      method: "POST",
                      body: data
                    }

  try {
    const response    =   await fetch(Config.ConfigApirest + "get?modulo="+modulo+"&m="+m+"&formato=json&u="+me.token,cabecera);
    const json        =   await response.json();
    if (json.response.callback!=undefined) {
      eval(data.response.callback+"(data.response)");
    }
    return json;
  }catch (error) {
    return error;
  }
}


const Post      =   (modulo,m,objeto,context) =>{
  let me        =   (Store.get("user")!=null)?Store.get("user"):{token:"PGRW_REGISTER"};
  let headers   =   new Headers();
  let data      =   new FormData();

  Object.entries(objeto).map((v,k) => {
    data.append (v[0],v[1]);
  })

  data.append ("u", me.token);
  data.append ("token", me.token);

  data.append ("PUBLIC_KEY", Config.PUBLIC_KEY);

  if (Store.get("security")===null) {
    data.append ("PRIVATE_KEY", Config.PRIVATE_KEY);
  }else {
    data.append ("tokens_access",Store.get("security"));
  }

  let cabecera  =   {
                      headers:headers,
                      method: "POST",
                      body: data
                    }

  fetch(Config.ConfigApirest + "get?modulo="+modulo+"&m="+m+"&formato=json&u="+me.token,cabecera)
        .then(response  =>  response.json()        )
        .then(data      =>  { (data.response.callback!==undefined)?eval(data.response.callback+"(data.response,context)"):console.log(data) })
        .catch((error)  =>  { console.log(error)  });
}

const dialog = (data,context)=>{

  context.setState({
                        dialog:{
                                  status:true,
                                  title:"Importante",
                                  message:data.message,
                                  callback:false
                        }
                    })

  //console.log(data,context);
}

const setTokenStore=(data)=>{
  Store.set("security",data.data);
}


export default {  CutString,
                  FechaHoy,
                  Convertir_base64,
                  Get,
                  Post,
                  PostAsync,
                  InputTypeFilter
                }
