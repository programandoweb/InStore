import React from 'react';

function get(index){
  let get = localStorage.getItem(index);
  return JSON.parse(get)
}

function set(index,object){
  localStorage.getItem(index,JSON.stringify(object));
}

export default {get,set}
