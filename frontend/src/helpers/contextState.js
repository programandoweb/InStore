import React from 'react'
const StateContext = React.createContext(true)
export const StateProvider = StateContext.Provider
export const StateConsumer = StateContext.Consumer
export default StateContext
