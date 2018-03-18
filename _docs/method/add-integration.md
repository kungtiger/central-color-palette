---
title: add_integration
category: method
signature: 'add_integration( $id, $options = "" )'
synopsis: Adds a palette integration

returns:
  -
    type: boolean
    description: Returns true on success, false if an integration with the given id is already added or any of the options are incomplete

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
    name: "enabled"
    type: "boolean|callback"
    default: "true"
    description: A check whether the integration is enabled. Can be a boolean or a callback
  -
    name: "palette"
    type: "callback"
    description: If the integration is active this callback is called during init
  -
    name: "alpha"
    type: "boolean"
    default: "false"
    description: If true this integration supports alpha/transparency
  -
    name: "form"
    type: "callback"
    default: "null"
    description: If set this callback is used to generate form fields on the settings page

see:
  -
    path: _guides/integration.md
---