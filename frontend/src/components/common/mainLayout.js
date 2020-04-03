import React from 'react';
import '../../App.css';
import { BrowserRouter, Route, Switch } from "react-router-dom";
import Header from "./header";
import Home from "./home";
import Footer from "./footer";


function HomeLayout(){
  return(<div><Header/><Home/><Footer/></div>)
}

function App() {
  return (
    <BrowserRouter className="main">
      <Switch>
        <Route exact path="/" render={props => <HomeLayout {...props} />} />
      </Switch>
    </BrowserRouter>
  );
}

export default App;
