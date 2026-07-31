# Technical Guidelines & Development Concepts

## Frontend Performance Optimization

### Lazy Loading

Use lazy loading to load components, images, or resources only when they are needed.

Benefits:
- Improves initial page load speed.
- Reduces unnecessary data loading.
- Improves user experience.

Example:
- Load dashboard pages only when the user navigates to them.

---

### Code Splitting

Split the application code into smaller chunks instead of loading the entire application bundle at once.

Benefits:
- Reduces initial JavaScript bundle size.
- Improves application startup time.
- Loads only required code when needed.

Example:
- Separate chunks for Dashboard, Reports, Settings, and other modules.

---

### Minification & Compression

Minification:
- Remove unnecessary characters from code such as spaces and comments.
- Performed during the production build process.

Compression:
- Reduce file size during network transfer using algorithms such as Gzip or Brotli.

Both improve loading performance.

---

## React Performance Guidelines

### Avoid Unnecessary Re-renders

Too many React re-renders can negatively affect performance.

Use:
- React.memo for preventing unnecessary component updates.
- useMemo for expensive calculations.
- useCallback for stable function references.
- Proper component separation.

---

### Debouncing and Throttling

#### Debouncing

Execute a function only after the user stops triggering an event.

Use cases:
- Search inputs.
- API calls while typing.

Example:
User types "Toyota":
- Wait until typing stops.
- Send one API request.

#### Throttling

Limit how often a function can execute during continuous events.

Use cases:
- Scroll events.
- Window resizing.
- Drag events.

---

## API Communication

### HTTP Methods

GET:
- Retrieve data from the server.

POST:
- Create new data.

PUT/PATCH:
- Update existing data.

DELETE:
- Remove data.

---

### Request Structure

An HTTP request contains:

#### Params

Data sent through the URL.

Examples:
- Resource IDs.
- Filtering.
- Pagination.

Example:
`/cars?page=2&limit=10`

---

#### Headers

Additional information about the request.

Examples:
- `Authorization: Bearer token`
- `Content-Type: application/json`

Used for:
- Authentication.
- Request metadata.

---

#### Body

Main data sent to the server.

Usually used with:
- POST
- PUT
- PATCH

Example:
```json
{
  "carId": 10,
  "date": "2026-08-01"
}
```
