---
"@cambis/silverstan": major
---

- Add custom autoloader and configuration resolution. The intention here is to allow this extension to not be tied to any specific Silverstripe version. This will also allow us to use PHPStan 2.0 with Silverstripe 5.x, currently the two are not compatitable. These features are currently opt-in using the bleedingEdge config but will become standard in 2.0
- Various bugfixes
