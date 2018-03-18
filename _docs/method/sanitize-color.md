---
title: sanitize_color
category: method
priority: 15
signature: 'sanitize_color( $string, $prepend_hash = true )'
arguments:
  -
    name: string
    type: string
    description: The input string to be sanitized
  -
    name: prepend_hash
    type: boolean
    default: "true"
    description: If `true` the resulting string will be prepended by a hash

returns:
  -
    type: string
    description: If sanitized successfully the color will be returned
  -
    type: false
    description: If the color can not be sanitized, false is returned
---