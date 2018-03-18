---
title: add_luma_type
category: method
signature: 'add_luma_type( $id, $options = "" )'
synopsis: Adds a luma type

returns:
  -
    type: boolean
    description: Returns true on success, false if a luma type with the given id is already added or any of the options are incomplete

arguments:
  -
    name: id
  -
    name: options

options:
  -
    name: "name"
    type: "string"
    default: "id"
    description: The name for display. Defaults to the id with the first character capitalized
  -
    name: "fn"
    type: "callback"
    description: A callback that transforms the luma values

callbacks:
  -
    name: Transformation Callback Function
    arguments:
      -
        name: luma
        type: float
        description: Linear float between -1 and 1
      -
        name: type
        type: string
        description: ID of the luma type
    returns:
      -
        type: float
        description: Should return a float between -1 and 1

see:
  -
    path: _guides/luma.md
---