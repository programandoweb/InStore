import React from 'react';
import { makeStyles } from '@material-ui/core/styles';
import MuiImageSlider from 'mui-image-slider';

const useStyles = makeStyles((theme) => ({
  root: {
    flexGrow: 1,
    width:"100%",
  },
  wrapper:{
    height: '20vh',
  },
  img:{
    height: '100%',
  },
}))

const images = [
    'http://www.todoautos.com.pe/portal/images/stories/00_Fotos_Noticias/escoge-tu-carro-tipo-vehiculo-1.jpg',
    'http://www.todoautos.com.pe/portal/images/stories/00_Fotos_Noticias/escoge-tu-carro-tipo-vehiculo-2.jpg',
    'http://www.todoautos.com.pe/portal/images/stories/00_Fotos_Noticias/escoge-tu-carro-tipo-vehiculo-5.jpg',
    'http://www.todoautos.com.pe/portal/images/stories/00_Fotos_Noticias/escoge-tu-carro-tipo-vehiculo-10.jpg',
];

function App() {
  const classes = useStyles();
  return (
    <div style={{root: classes.root, }}>
      <MuiImageSlider classes={{root: classes.root, }}  images={images}/>
    </div>
  );
}

export default App;
