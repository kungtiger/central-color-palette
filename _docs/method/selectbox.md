---
title: selectbox
category: method
priority: 15
since: "1.7"
synopsis: Returns HTML markup of a selectbox
signature: 'selectbox( $name, $data, $selected = null, $disabled = false )'
returns:
  -
    type: string
arguments:
  -
    name: name
    type: string
    description: The name and also the id of the selectbox
  -
    name: data
    type: array
    description: Array of available options
  -
    name: selected
    type: string
    default: "null"
    description: The currently selected option
  -
    name: disabled
    type: boolean
    default: "false"
    description: If true the selectbox is disabled
---