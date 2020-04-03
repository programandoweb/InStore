import React from 'react';
import { BrowserRouter, Route, Switch } from "react-router-dom";
import {MainLayout  , LoginLayout , RegisterLayout , RecoverLayout ,AlertDialogSlide} from "./components/common";
import Store from "./helpers/store";
import {AdminLayout} from "./components/admin/";
import StateContext from './helpers/contextState';

class App extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
                    loading: true,
                    loggedIn: false,
                    dialog:{
                              status:false,
                              title:"Título de la ventana",
                              message:"Prueba de mensaje",
                              callback:false
                    }
                  };
    this._setState = this._setState.bind(this);
  }

  componentDidMount() {
    this.loggedIn();
  }

  componentWillUnmount() {
    this.loggedIn();
  }

  _setState = (data)  =>  {
    this.setState(data)
  }

  loggedIn=()=>{
    if (Store.get("user")==null) {
      this.setState({loggedIn:false})
    }else {
      this.setState({loggedIn:true,user:Store.get("user")})
    }
  }

  __loginProps=()=>{
    return {
              setState:this.setState,
              Store:Store,
          }
  }

  render() {
    return (
      <StateContext.Provider value={{setState:this._setState,state:this.state}}>
        <BrowserRouter>
          <AlertDialogSlide methods={{setState:this._setState,state:this.state}}/>
          <Switch>
            <Route exact path="/" render={props => <MainLayout {...props} />} />
            <Route  path="/admin"
                    render={  (props) => this.state.loggedIn ? <AdminLayout  {...props}/>:<LoginLayout methods={this.__loginProps()}/> }
            />
          <Route exact path="/auth/login" render={props => <LoginLayout {...props} />} />
            <Route exact path="/auth/register" render={props => <RegisterLayout {...props} />} />
            <Route exact path="/auth/recover" render={props => <RecoverLayout {...props} />} />
          </Switch>
        </BrowserRouter>
      </StateContext.Provider>
    );
  }
}

export default App;
