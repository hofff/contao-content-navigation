# Contao Content Navigation Element

This extension provides a table of contents for elements based on one or more articles within the page.

This extension was previously published as *ce_navigation* and maintained by Tristan Lins.

*Although the extension is based on ce-navigation, it is not backwards compatible. Configurations must be renewed.*

## Requirements

 - Contao: ^4.9
 - PHP: ^7.1 || ^8.0
 
## Usage

 - Create a content element "Table of contents" and define the source
 - Active option "Include in table of contents" for each content element

Now you get an content navigation for each element marked as included and having a headline.

You can define the source for the table of content structure depending where the content element is included:

 - Article: Column, Specific article or parent element (current article)
 - News: Parent element (news)
 - Event: Parent element (event)

## Foldable navigation

Select the template `hofff_content_nav_foldable` in the *Navigation template* field to make the sub
levels foldable. Every entry having children gets a toggle button, which can be operated with mouse
and keyboard and carries the according `aria-expanded`, `aria-controls` and `aria-label` attributes.

The navigation is rendered expanded and is collapsed by the script afterwards, so that all entries
remain reachable without JavaScript. When the page is opened with an anchor, the ancestors of the
linked entry are expanded and the entry is marked with the class `toc-current`. Each entry carries
its anchor in `data-toc-id`.

The required assets are registered automatically for templates whose name starts with
`hofff_content_nav_foldable`, so custom copies keeping that prefix are covered as well. Templates
named differently have to add `bundles/hofffcontentnavigation/foldable.js` and `foldable.css`
themselves.

The shipped stylesheet only indents the sub levels and draws the caret. Styling beyond that is left
to the theme, the following hooks are available:

| | |
|---|---|
| `.toc-toggle` | the toggle button, `aria-expanded` reflects the state |
| `.toc-has-children` | entry having sub items |
| `.toc-expanded` | entry whose sub items are currently visible |
| `.toc-current` | entry the current anchor points to |
 - FAQ: Parent element (FAQ entry)
 
## Customization

If your 3rd party module also works like the news or events module (category defining a `jumpTo` page and an entry 
holding content elements), you can enable support for your own module by adding it to the 
`hofff_contao_content_navigation.jump_to_relations` parameter.

## Known limitations

Only content elements of the direct source are recognized. Including articles or modules are not supported right now.
That means, you *cannot* define a table of contents element in an article and building a content navigation for a news
entry included by a news reader module. 
