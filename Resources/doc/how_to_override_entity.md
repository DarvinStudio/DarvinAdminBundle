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

5. Если заменяемая сущность выступает в роли "targetEntity" в маппинге, что легко определить по реализации интерфейса,
 соответствующего названию класса (см п. 2), необходимо указать замену класса в разделе "orm.resolve_target_entities" конфигурации
 бандла "DoctrineBundle":

```yaml
doctrine:
    orm:
        resolve_target_entities:
            Darvin\ECommerceBundle\Entity\Product\ProductInterface: AppBundle\Entity\ECommerce\Product\AppProduct
```

6. Необходимо заменить класс в поле "object_class" таблицы "content_slug_map" базы данных.
