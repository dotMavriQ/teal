# Themes

TEAL uses a CSS custom property system for theming. Themes are defined as data attributes on the root element, and Tailwind maps those properties to utility classes.

## Available Themes

### Normie (Default)

A clean light theme with standard grays and blues. Nothing fancy, gets out of your way.

- Backgrounds: white to light gray
- Text: dark gray
- Accent: blue
- This is the "I just want it to work" option.

### Gruvbox Dark

A warm dark theme based on the [gruvbox](https://github.com/morhetz/gruvbox) color scheme. If you spend time in terminals and text editors, you probably already know this one.

- Backgrounds: `#282828` to `#665c54`
- Text: `#ebdbb2` (the classic gruvbox foreground)
- Accent: `#83a598` (gruvbox blue)
- Status colors use the gruvbox palette

## How It Works

### CSS Layer

Themes are defined in `resources/css/app.css` using `[data-theme="theme-name"]` selectors. Each theme sets the same set of CSS custom properties:

```css
[data-theme="gruvbox-dark"] {
    --color-bg-primary: 40 40 40;
    --color-text-primary: 235 219 178;
    --color-accent-primary: 131 165 152;
    /* ... */
}
```

Values are stored as raw RGB triplets (no `rgb()` wrapper) so Tailwind can inject alpha values for opacity utilities.

### Tailwind Layer

`tailwind.config.js` maps these properties to color utilities:

```js
colors: {
    theme: {
        'bg-primary': 'rgb(var(--color-bg-primary) / <alpha-value>)',
        'text-primary': 'rgb(var(--color-text-primary) / <alpha-value>)',
        // ...
    }
}
```

This lets you write `bg-theme-bg-primary` or `text-theme-text-secondary` in Blade templates and have it resolve to whatever the current theme defines.

### Theme Config

Theme definitions and metadata live in `config/themes.php`.

## CSS Variable Reference

Every theme must define all of these variables. If you are adding a new theme, copy an existing one and change the values.

### Backgrounds
- `--color-bg-primary` -- Main background
- `--color-bg-secondary` -- Page background (behind cards)
- `--color-bg-tertiary` -- Subtle contrast areas
- `--color-bg-hover` -- Hover states
- `--color-bg-active` -- Active/pressed states

### Text
- `--color-text-primary` -- Headings, important text
- `--color-text-secondary` -- Body text
- `--color-text-tertiary` -- Less important text
- `--color-text-muted` -- Disabled, placeholder
- `--color-text-inverted` -- Text on dark buttons

### Borders
- `--color-border-primary` -- Standard borders
- `--color-border-secondary` -- Subtle dividers
- `--color-border-focus` -- Focus ring color

### Buttons
- `--color-btn-primary-bg`, `hover`, `text`
- `--color-btn-secondary-bg`, `hover`, `text`, `border`
- `--color-btn-danger-bg`, `hover`, `text`

### Status Colors
- `--color-status-want`, `--color-status-want-bg` -- Want to Read
- `--color-status-reading`, `--color-status-reading-bg` -- Currently Reading
- `--color-status-read`, `--color-status-read-bg` -- Read
- `--color-status-watchlist`, `--color-status-watchlist-bg` -- Watchlist
- `--color-status-watching`, `--color-status-watching-bg` -- Watching
- `--color-status-watched`, `--color-status-watched-bg` -- Watched

### Feedback
- `--color-success`, `--color-success-bg`
- `--color-warning`, `--color-warning-bg`
- `--color-danger`, `--color-danger-bg`
- `--color-info`, `--color-info-bg`

### Inputs
- `--color-input-bg`, `text`, `border`, `focus-ring`, `placeholder`

### Cards
- `--color-card-bg`, `card-border`

### Other
- `--color-star-filled`, `--color-star-empty` -- Rating stars
- `--color-link`, `--color-link-hover`
- `--color-accent-primary`, `--color-accent-primary-hover`, `--color-accent-secondary`
- `--color-shadow`, `--color-shadow-opacity`

## Adding a New Theme

1. Add a new `[data-theme="your-theme"]` block in `resources/css/app.css`.
2. Define every variable listed above.
3. Add the theme to `config/themes.php`.
4. Run `npm run build` to recompile.
