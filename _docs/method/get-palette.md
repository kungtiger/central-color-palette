---
title: get_palette
category: method
signature: 'get_palette( $options = "" )'
synopsis: "Returns the color palette"
returns:
  -
    type: array
    description:

arguments:
  -
    name: options
    type: string|array
    default: '""'

options:
  -
    name: alpha
    type: boolean
    default: current settings value
    description: If true return colors in rgba notation
  -
    name: min
    type: integer
    default: 6
    description: Minimal number of colors the palette should contain
  -
    name: pad
    type: string
    default: "#FFFFFF"
    description: The color to pad the palette
---
