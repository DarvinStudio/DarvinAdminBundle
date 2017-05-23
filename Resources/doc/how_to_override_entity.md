Как переопределить класс сущности
=================================

**Внимание**: _данный способ работает только для сущностей, которые создаются исключительно средствами панели администрирования._

1. Заменяемый класс должен быть аннотирован "@ORM\InheritanceType("SINGLE_TABLE")", его свойства и методы должны иметь
 область видимости "protected".

2. Если заменяемый класс фигурирует в маппинге как "targetEntity", он должен реализовывать интерфейс, соответствующий названию
 класса (например, "Product" - "ProductInterface").

3. Заменяющий класс должен иметь другое название (можно, например, добавить префикс "App") для корректной генерации
 таблицы дискриминаторов (не забудьте обновить дискриминаторы уже созданных сущностей в таблице базы данных, как правило
 это поле "dtype", пример значения - "AppProduct" - "appproduct").

4. Нужно обозначить замену класса в разделе "entity_override" конфигурации бандла "DarvinAdminBundle":

```yaml
darvin_admin:
    entity_override:
        Darvin\ECommerceBundle\Entity\Product\Product: AppBundle\Entity\ECommerce\Product\AppProduct
```

Замена будет добавлена автоматически, если FQCN заменяющего класса соответствует шаблону
 "AppBundle\Entity\\{BUNDLE_NAME}\\{CLASS_NAMESPACE}\App{CLASS_NAME}", где:

- **BUNDLE_NAME** - название бандла заменяемой сущности без префикса "Darvin" и суффикса "Bundle", например, "ECommerce";
- **CLASS_NAMESPACE** - неймспейс класса заменяемой сущности после части "Entity", например, "Product" (может отсутствовать);
- **CLASS_NAME** - название класса заменяемой сущности с префиксом "App", например, "AppCatalog".

Примеры:

- "Darvin\ECommerceBundle\Entity\Product\Catalog" => "AppBundle\Entity\ECommerce\Product\AppCatalog";
- "Darvin\PageBundle\Entity\Page" => "AppBundle\Entity\Page\AppPage".

Проконтролировать, что класс подцепился, можно, посмотрев сгенерированное содержимое секции "entity_override"
 конфигурации данного бандла с помощью

```shell
$ php app/console debug:config darvin_admin
```

5. Если заменяемый класс является переводом, необходимо переопределить его метод "getTranslatableEntityClass()", а также
 метод "getTranslationEntityClass()" переводимой сущности, тем самым установив корректную связь.

6. Необходимо заменить класс в поле "object_class" таблицы "content_slug_map" базы данных.
