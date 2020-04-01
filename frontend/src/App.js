import React from 'react';
import ReactDOM from "react-dom";
import { BrowserRouter, Route, Switch, Redirect } from "react-router-dom";
import {MainLayout  , LoginLayout , RegisterLayout , RecoverLayout} from "./components/common";
import Store from "./helpers/store";
import TeachersLayout from "./components/common/home";
import {AdminLayout} from "./components/admin/";

class App extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
                    loading: true,
                    loggedIn: false,
                  };
  }

  componentDidMount() {
    this.loggedIn();
  }

  componentWillUnmount() {
    this.loggedIn();
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
      <BrowserRouter>
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
    );
  }
}

export default App;
