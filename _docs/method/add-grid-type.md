---
title: add_grid_type
category: method
signature: 'add_grid_type( $id, $options = "" )'
synopsis: Adds a grid type

returns:
  -
    type: boolean
    description: Returns true on success, false if a grid type with the given id is already added or any of the options are incomplete

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
    name: "render"
    type: "callback"
    description: A callback that generates the color map for the Visual Editor

callbacks:
  -
    name: Render Callback Function
    arguments:
      -
        name: instance
    returns:
      -
        type: array
        description: An array containing the color map and its size

see:
  -
    path: _guides/grid-type.md
---