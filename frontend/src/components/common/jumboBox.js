import React from 'react';

const image = 'images/banner.jpg';

const divStyle = {
  backgroundImage: 'url(' + image + ')',
  backgroundPosition: 'center',
  backgroundSize: 'cover',
  backgroundRepeat: 'no-repeat'
};

function App() {
  return (
    <div style={divStyle}>
      <div className="jumboBox">
        sadsa
      </div>
    </div>
  );
}

export default App;
