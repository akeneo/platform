OroEntityBundle
========================

- [Form Components Overview](./Resources/doc/form_components.md)


    Example for Resources/config/entity_extend.yml
    TestClassExtend:
        configs:
            entity:
                label:                  TestClassExtend
        fields:
            testStringField:
                type:                   string
                configs:
                    entity:
                        label:          testStringField
                options:
                    length:             200
            testIntegerField:
                type:                   smallint
            testHiddenField:
                mode:                   hidden
                type:                   string
            testReadonlyField:
                mode:                   readonly
                type:                   string

    Oro\Bundle\UserBundle\Entity\User:
        fields:
            testField:
                type:                   string
            testHiddenField:
                mode:                   hidden
                type:                   string
            testReadonlyField:
                mode:                   readonly
                type:                   string
                
**Entity Manager**

In order to extend some native Doctrine Entity Manager functionality a new class `OroEntityManager` was implemented.
In case any other modification are required, your class should extend `OroEntityManager` instead of Doctrine Entity Manager.
