---
title: sanitize_color
category: method
priority: 15
signature: 'sanitize_color( $string )'
arguments:
  -
    name: string
    type: string
    description: The input string to be sanitized
returns:
  -
    type: string
    description: If sanitized successfully the color will be returned
  -
    type: false
    description: If the color can not be sanitized, false is returned
---

This method takes an input string and tries to find a hexadecimal color with optional leading hash. If the found color is in short notation it is expanded. The resulting string has a prepended hash character.