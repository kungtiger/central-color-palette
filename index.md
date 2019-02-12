---
layout: default
---

## get_colors( [array $options] ) : array

Get the colors of the central palette.

```php
kt_Central_Palette::instance()->get_colors();
```

Option | Type | Default | Description
------ | ---- | ------- | -----------
`alpha` | bool | `false` | If `true` and a color of the palette has a transparency less than 100 the color will be returned in CSS `rgba()` notation
`min` | bool or int | `false` | The number of colors that should at least be returned
`pad` | string | `"#FFFFFF"` | If the palette has less than `min` colors pad by this
`hash` | bool | `true` | Whether to prepend each color by an hash (`#`) or not
`default` | mixed | *empty array* | If the palette is empty return this instead

## get_palette( [array $options] ) : array

Get the central palette.

```php
kt_Central_Palette::instance()->get_palette();
```

Option | Type | Default | Description
------ | ---- | ------- | -----------
`status` | bool, int or array | `false` | Filter the colors by status.
`chunk` | false or int | `false` | Chunk palette into columns of constant size.
`pad` | mixed | *black* | Pad the last column by this color to the length of `chunk`.

## set_palette( array $colors [, boolean|float $merge_threshold = 0.25] ) : array

Set the central palette.

Argument | Type | Default | Description
-------- | ---- | ------- | -----------
`$colors` | array | *required* | The color palette
`$merge_threshold` | `false` or float | `0.25` | See further down for details

```php
kt_Central_Palette::instance()->set_palette(array( "#222299", "6DE", "#777" ... ));

kt_Central_Palette::instance()->set_palette(array(
    array(
        "color" => "#222299",
        "name" => "Night",
        "alpha" => 80,
        "status" => kt_Central_Palette::COLOR_ACTIVE,
        "index" => 9,
    ),
    ...
));
```

You can can define each color as a string, but if you need more refinement pass an array instead of an string. This array needs at least a `color` entry and all others are optional. Any invalid color will be silently ignored.

Option | Type | Default | Description
------ | ---- | ------- | -----------
`color` | string | *required* | Valid colors are defined by the regular expression `#?{[0-9a-fA-F]{3}|[0-9a-fA-F]{6}}`
`name` | string | *empty string* | Pretty self-explanatory
`alpha` | int | `100` | An integer between 0 and 100
`status` | int | `kt_Central_Palette::COLOR_ACTIVE` | `kt_Central_Palette::COLOR_ACTIVE` or `kt_Central_Palette::COLOR_INACTIVE`
`index` | int | *auto* | See further down for details

### Merge Threshold and Palette Indices

Each color has an unique index that is used to generate CSS class names for WordPress' new Block Editor. These indices maintain the relation between a color and a block. In order to minimize chaos that can arise when a palette is set it is merged with the current one in order to reuse indices. To determine if an index should be reused new colors are checked against current ones and if found close enough that index is reused. You can set this merge threshold by passing an additional second argument between 0 and 100, whereby 0 means an exact match and 100 any. The default is 0.25, so quite a close match is needed. If a merge threshold is set any indices that are set through the `index` entry of a color will be ignored.

If you do not want for any merge to happen simply pass `false` as second argument. In that case make sure your colors have unique indices or none at all. The method does not check for index collisions. In any case a color missing an index is automatically assigned one.