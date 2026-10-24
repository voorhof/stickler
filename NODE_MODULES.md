# Node Modules Dependencies

All frontend packages used in this project are installed with **npm** and defined in `package.json`.
Assets are compiled with **Vite**.

```bash
npm install
npm run dev
npm run build
```

## Bootstrap
[Documentation](https://getbootstrap.com/docs/5.3/)  
[GitHub](https://github.com/twbs/bootstrap)

The CSS framework used for the layout and UI components.
Styles are imported in `resources/scss/app.scss`, and the Dropdown and Offcanvas JavaScript components are imported in `resources/js/bootstrap/bootstrap.js`.
Unused components are left commented out to keep the bundle small.

## Popper (`@popperjs/core`)
[Documentation](https://popper.js.org/docs/v2/)  
[GitHub](https://github.com/popperjs/popper-core)

The positioning engine required by Bootstrap's Dropdown component.
It is imported in `resources/js/bootstrap/bootstrap.js` and exposed globally as `window.Popper`.

## Bootstrap Icons
[Documentation](https://icons.getbootstrap.com/)  
[GitHub](https://github.com/twbs/icons)

An icon set from the Bootstrap team.
The package is installed, but it is not currently imported in the Sass or JavaScript files.

## Sass
[Documentation](https://sass-lang.com/documentation/)  
[GitHub](https://github.com/sass/dart-sass)

Compiles the SCSS files, including Bootstrap's source and the project customizations in `resources/scss/bootstrap/_variables-custom.scss`.
Compiler deprecation warnings for Bootstrap are silenced in `vite.config.js`.

## Vite
[Documentation](https://vite.dev/guide/)  
[GitHub](https://github.com/vitejs/vite)

The build tool and development server.
It is configured in `vite.config.js`, and `npm run dev` runs it with hot module replacement.

## Laravel Vite Plugin
[Documentation](https://laravel.com/docs/13.x/vite)  
[GitHub](https://github.com/laravel/vite-plugin)

Connects Vite to Laravel.
It defines the entry points (`resources/scss/app.scss` and `resources/js/app.js`), the asset folders, and the Google Font `Inter` configuration in `vite.config.js`.

## Fontaine
[GitHub](https://github.com/unjs/fontaine)

An optional peer dependency of `laravel-vite-plugin`.
It generates fallback font metrics for the `Inter` font, which reduces layout shift while the web font loads.

## Rollup Plugin Replace
[Documentation](https://github.com/rollup/plugins/tree/master/packages/replace)  
[GitHub](https://github.com/rollup/plugins/tree/master/packages/replace)

Used in `vite.config.js` to replace `data-bs` with `data` in the build output.

## Development-only formatting plugins

The following packages are installed for maintaining frontend source files but are not part of the browser bundle:

- **Prettier** (`prettier`) — formats JavaScript, JSON, and other supported source files.
- **Prettier Blade Plugin** (`prettier-plugin-blade`) — adds Blade template formatting support.
- **Prettier Tailwind CSS Plugin** (`prettier-plugin-tailwindcss`) — sorts Tailwind utility classes when Tailwind syntax is present.

These packages are configured as `devDependencies` in `package.json` and currently do not have npm scripts in this starter kit.
