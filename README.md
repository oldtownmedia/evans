# Evans

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/oldtownmedia/evans/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/oldtownmedia/evans/?branch=master)

Evans is our functionality mu-plugin for all sites that we build. Evans handles most pieces of functionality that we consistently reuse on sites - custom post types, setup scripts, common functions, etc. 

### How to use

The mu-plugin can simply be downloaded and placed your wp-content folder and will work automatically without any modifications. However, Evans is very opinionated and by default includes setup scripts that could override existing settings if you do not delete the site-setup files first. Decide what functionality you would like to keep first and delete the unnecessary files - Evans will know what to load and the order in which to load it.

### Why a mu-plugin?

We settled on a mu-plugin over a regular WordPress plugin for several reasons:

1. We don't need the portability of a regular plugin - we're developers and it's actually less steps to install a mu-plugin
2. Keeps important, potentially site-breaking functionality in place and cannot be deleted by an accidental click
3. Consistency in loading order and priority

### History

Several years ago we found ourselves constantly repeating several pieces of functionality for client sites and doing so in an inconsistent manner. So we decided to build a functionality plugin that handled these cases for us - it would have default custom post types, functions that we commonly used, even some setup scripts so that we didn't have to click around when we created a site. 

This worked very well and rapidly evolved over several years but started to show its age greatly in 2015 and we decided to scrap and rebuild form the ground up - a fresh V2.0. That's where Evans comes in. 


## Extending

### CPT



### Widget



### Dashboard Widget



### Test Data