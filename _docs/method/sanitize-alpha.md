---
title: sanitize_alpha
category: method
priority: 15
signature: 'sanitize_alpha( $string )'
arguments:
  -
    name: string
    type: string|integer
    description: The input string to be sanitized
returns:
  -
    type: integer
    description:
---

This method takes an input string and sanitizes it to a valid alpha/transparency value, that is a integer between 0 and 100 inclusive. If the input string can not be sanitized or lays outside the valid range 100 is returned.