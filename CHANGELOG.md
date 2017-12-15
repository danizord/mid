# Changelog

## 0.2.0

### Added

- Support for http-interop/http-server-middleware and http-interop/http-server-handler
- `path()` utility function that decorates a middleware and only runs it if the incoming
request URI matches the required given path prefix.

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- [BC Break] Support for http-interop/http-middleware

### Fixed

- Nothing.

## 0.1.0

### Added

- Lightweight middleware pipeline implementation
- Lazy middleware pipeline setup
- Function to transform middlewares into request handlers and vice-versa
- Wrappers for callable middlewares and request handlers

### Changed

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.
