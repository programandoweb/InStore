import React, { useState } from 'react';
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
import Config from "../../helpers/config";
import useStyles from "../../helpers/useStyles";
import StateContext from '../../helpers/contextState'
import Copyright from './copyright'



export default function SignInSide(props) {
  const [value, setInputs] = useState(0);
  const classes = useStyles();

  function handleChange(event){
    let name  = event.target.name;
    let obj   = {
      [name]: event.target.value,
    }
    setInputs(obj);
  };

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
            Recuperar Contraseña
          </Typography>
          <form className={classes.form} noValidate>
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
            <Button
              type="submit"
              fullWidth
              variant="contained"
              color="primary"
              className={classes.submit}
            >
              Recuperar Contraseña
            </Button>
            <Grid container>
              <Grid item xs>
                <Link href={Config.ConfigAppUrl+"auth/login"} >
                  Iniciar Sesión
                </Link>
              </Grid>
              <Grid item>
                <Link href={Config.ConfigAppUrl+"auth/register"} >
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
