# etsy-phan-example

A playground for me to tinker with [etsy/phan], and potentially find the best
setup to use the tool.

## Requirements

- PHP 7
- [nikic/php-ast](https://github.com/nikic/php-ast)

If you're all set on those two requirements, you can set up your environment
via:

```bash
script/bootstrap
```

## Issue with parsing versus analyzing

As for 2017-01-02, this project is pretty simple:

```
etsy-phan-example
├── src
│   ├── A.php
│   └── B.php
└── vendor
    └── …
```

…with `B.php` depending on symbols in `A.php`, and both `A.php` & `B.php`
depending on various symbols within `vendor/`. Using the [etsy/phan] parlance,
it would seem I want to _parse_ `A.php`, `B.php`, and `vendor/**/*.php`, but
only _analyze_ all or a subset of `A.php` and `B.php` (the code I control).

To _parse_ files, you want to send them in as a `--directory` option to `phan`.
These files are also _analyzed_, but you wouldn't want to analyze code you
don't control (e.g., third-party vendor code), so despite wanting to parse
those symbols you'll want to omit their analysis via the
`--exclude-directory-list` flag.

To parse and analyze our entire codebase:

```bash
> ./vendor/bin/phan --directory ./src --directory ./vendor --exclude-directory-list ./vendor
./src/B.php:12 PhanNoopProperty Unused property
```

**The problem** on my end is **you don't seem to be able to analyze just a single source file**:

```bash
# passing src/B.php into `phan` since that's the file I want to analyze, but
# including the entire src directory for parsing since B.php depends on symbols
# in A.php.
> ./vendor/bin/phan --directory ./src --directory ./vendor --exclude-directory-list ./vendor src/B.php
./src/B.php:12 PhanNoopProperty Unused property
src/B.php:5 PhanRedefineClass Class \My\Example\B defined at src/B.php:5 was previously defined as Class \My\Example\B at ./src/B.php:5
```

The above is warning me that we're parsing `\My/Example\B` twice. Ok, so let's
omit the `--directory ./src` argument:

```bash
> ./vendor/bin/phan --directory ./vendor --exclude-directory-list ./vendor src/B.php
src/B.php:8 PhanUndeclaredClassMethod Call to method __construct from undeclared class \My\Example\A
src/B.php:8 PhanUndeclaredClassMethod Call to method getStatuscode from undeclared class \My\Example\A
src/B.php:12 PhanNoopProperty Unused property
```

Now I'm seeing errors due to our ignorance of `\My\Example\A`.

**How can I parse all of my dependencies (entire code base I control + entire
third-party codebase), but only analyze the files I want on demand?** The use
case is sometimes I want to analyze my entire codebase, but other times I only
want to analyze a few files (e.g., for speed, or after some edits, etc.).

[etsy/phan]: https://github.com/etsy/phan

## Issue with speed

This is a pretty minimal project, and analyzing the codebase takes close to 7
seconds:

```
❯ time ./vendor/bin/phan --directory ./src --directory ./vendor --exclude-directory-list ./vendor
./src/B.php:12 PhanNoopProperty Unused property
./vendor/bin/phan --directory ./src --directory ./vendor  ./vendor  6.62s user 0.37s system 98% cpu 7.083 total
```

When using the `--processes` argument to try to speed things up by using more
CPU cores, it's slower :-/

```
❯ time ./vendor/bin/phan --directory ./src --directory ./vendor --exclude-directory-list ./vendor --processes 4
./src/B.php:12 PhanNoopProperty Unused property
./vendor/bin/phan --directory ./src --directory ./vendor  ./vendor --processe  7.29s user 1.24s system 106% cpu 7.979 total
```

These tests were ran on a MacBook Pro (Retina, 15-inch, Mid 2015), 2.2 GHz
Intel Core i7, 16 GB RAM.
