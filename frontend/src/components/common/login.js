import React, { useState } from 'react';
import Avatar from '@material-ui/core/Avatar';
import Button from '@material-ui/core/Button';
import CssBaseline from '@material-ui/core/CssBaseline';
import TextField from '@material-ui/core/TextField';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import Checkbox from '@material-ui/core/Checkbox';
import Link from '@material-ui/core/Link';
import Paper from '@material-ui/core/Paper';
import Box from '@material-ui/core/Box';
import Grid from '@material-ui/core/Grid';
import LockOutlinedIcon from '@material-ui/icons/LockOutlined';
import Typography from '@material-ui/core/Typography';
import functions from "../../helpers/functions";
import Config from "../../helpers/config";
import useStyles from "../../helpers/useStyles";
import StateContext from '../../helpers/contextState'
import Copyright from './copyright'

export default function SignInSide(props) {
  const [value, setInputs] = useState(0);
  const classes = useStyles();
  const context             =   React.useContext(StateContext);

  function handleChange(event){
    let name  = event.target.name;
    let obj   = {
      [name]: event.target.value,
    }
    setInputs(obj);
  };

  function handleSubmit(event,value){
    event.preventDefault();
    let data={}
    if(document.getElementById("email").value===''){
      alert("Por favor complete los datos");
      return document.getElementById("email").focus();
    }else {
      data["email"] = document.getElementById("email").value;
      //setInputs({email:document.getElementById("email").value});
    }

    if(document.getElementById("password").value===''){
      alert("Por favor complete los datos");
      return document.getElementById("password").focus();
    }else {
      data["password"] = document.getElementById("password").value;
      //setInputs({password:document.getElementById("password").value});
    }
    functions.Post("User","Login",data,context);
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
            Iniciar Sesión
          </Typography>
          <form className={classes.form} onSubmit={function(event){handleSubmit(event,value)}}  noValidate>
            <TextField
              variant="outlined"
              margin="normal"
              required
              fullWidth
              id="email"
              label="Correo Electrónico ó Celular "
              name="email"
              value={value.email}
              autoComplete="email"
              autoFocus
            />
            <TextField
              variant="outlined"
              margin="normal"
              required
              fullWidth
              name="password"
              value={value.email}
              label="Password"
              type="password"
              id="password"
              autoComplete="current-password"
            />
            <FormControlLabel
              control={<Checkbox value="remember" color="primary" />}
              label="Recordarme"
            />
            <Button
              type="submit"
              fullWidth
              variant="contained"
              color="primary"
              className={classes.submit}
            >
              Entrar
            </Button>
            <Grid container>
              <Grid item xs>
                <Link href={Config.ConfigAppUrl+"auth/recover"} >
                  Olvidé mi contraseña
                </Link>
              </Grid>
              <Grid item>
                <Link href={Config.ConfigAppUrl+"auth/register"}>
                  Crear una cuenta
                </Link>
              </Grid>
            </Grid>
            <Box mt={5}>
              <Copyright />
            </Box>
          </form>
        </div>
      </Grid>
    </Grid>
  );
}
