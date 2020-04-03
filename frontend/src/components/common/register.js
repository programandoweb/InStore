import React, { useState, useEffect } from 'react';
import Avatar from '@material-ui/core/Avatar';
import Button from '@material-ui/core/Button';
import CssBaseline from '@material-ui/core/CssBaseline';
import TextField from '@material-ui/core/TextField';
import Link from '@material-ui/core/Link';
import Paper from '@material-ui/core/Paper';
import Box from '@material-ui/core/Box';
import Grid from '@material-ui/core/Grid';
import LockOutlinedIcon from '@material-ui/icons/LockOutlined';
import Typography from '@material-ui/core/Typography';
import { makeStyles } from '@material-ui/core/styles';
import functions from "../../helpers/functions";
import Config from "../../helpers/config";
import Store from "../../helpers/store";
import StateContext from '../../helpers/contextState'

function Copyright() {
  return (
    <Typography variant="body2" color="textSecondary" align="center">
      {'Copyright © '}
      <Link color="inherit" href="https://material-ui.com/">
        Programandoweb
      </Link>{' '}
      {new Date().getFullYear()}
      {'.'}
    </Typography>
  );
}

const useStyles = makeStyles((theme) => ({
  root: {
    height: '100vh',
  },
  image: {
    backgroundImage: 'url(/images/software.png)',
    backgroundRepeat: 'no-repeat',
    backgroundColor:
      theme.palette.type === 'light' ? theme.palette.grey[50] : theme.palette.grey[900],
    backgroundSize: 'cover',
    backgroundPosition: 'center',
  },
  paper: {
    margin: theme.spacing(8, 4),
    display: 'flex',
    flexDirection: 'column',
    alignItems: 'center',
  },
  avatar: {
    margin: theme.spacing(1),
    backgroundColor: theme.palette.secondary.main,
  },
  form: {
    width: '100%', // Fix IE 11 issue.
    marginTop: theme.spacing(1),
  },
  submit: {
    margin: theme.spacing(3, 0, 2),
  },
}));

export default function SignInSide(props) {
  const [value,  setInputs]   =   useState({});
  const classes               =   useStyles();
  const [token,  setToken]    =   useState();
  const context             =   React.useContext(StateContext);

  useEffect(function getToken() {
    if (token===undefined && Store.get("security")===null) {
      setToken('waiting')
      let send  = {token_clone:Config.PRIVATE_KEY}
      functions.PostAsync("User","Token",send)
    }
  });

  function handleChange(event){
    let name      =   event.target.name;
    value[name]   =   functions.InputTypeFilter(event);
    setInputs(value);
  };

  function handleSubmit(event,value){
    event.preventDefault();

    if(document.getElementById("celular").value===''){
      alert("Por favor complete los datos");
      return document.getElementById("celular").focus();
    }

    if(document.getElementById("email").value===''){
      alert("Por favor complete los datos");
      return document.getElementById("email").focus();
    }

    value["submit"]   =   true;

    setInputs(value);

    if(value.password===value.password2){
      functions.Post("User","Register",value,context);              
    }else{
      alert("Por favor complete los datos")
    }
  }

  return (
    <Grid container component="main" className={classes.root}>
      <CssBaseline />
      <Grid item xs={false} sm={4} md={7} className={classes.image} />
      <Grid item xs={12} sm={8} md={5} component={Paper} elevation={6} square>
        <div className={classes.paper}>
          <Avatar className={classes.avatar}>
            <LockOutlinedIcon />
          </Avatar>
          <Typography component="h1" variant="h5">
            Crear cuenta de  Usuario
          </Typography>
          <form id="form" className={classes.form} onSubmit={function(event){handleSubmit(event,value)}} autoComplete="false">
            <TextField
                variant="outlined"
                margin="normal"
                required
                fullWidth
                id="celular"
                label="Celular"
                name="celular"
                autoComplete="celular"
                type="text"
                autoFocus
                onChange={handleChange}
                aria-describedby="component-error-text"
                inputProps={{maxLength:10,}}
              />
            <TextField
              required
              variant="outlined"
              margin="normal"
              required
              fullWidth
              id="email"
              label="Correo Electrónico"
              name="email"
              autoComplete="email"
              onChange={handleChange}
              data-validators="isEmail"
            />
            <TextField
              required
              variant="outlined"
              margin="normal"
              required
              fullWidth
              name="password"
              label="Password"
              type="password"
              id="password"
              autoComplete="current-password"
              onChange={handleChange}
            />
            <TextField
              required
              variant="outlined"
              margin="normal"
              required
              fullWidth
              name="password2"
              label="Repite el Password"
              type="password"
              id="password2"
              autoComplete="current-password"
              onChange={handleChange}
            />
            <Button
              type="submit"
              fullWidth
              variant="contained"
              color="primary"
              className={classes.submit}
            >
              Registrar Usuario
            </Button>
            <Grid container>
              <Grid item xs>
                <Link href={Config.ConfigAppUrl+"auth/login"}>
                  Iniciar Sesión
                </Link>
              </Grid>
              <Grid item>
                <Link href={Config.ConfigAppUrl+"auth/recover"}>
                  Recuperar cuenta
                </Link>
              </Grid>
            </Grid>
            <Box mt={5}>
              <Copyright />
            </Box>
          </form >
        </div>
      </Grid>
    </Grid>
  );
}
