<p align="center">

[![GitHub issues](https://img.shields.io/github/issues/cristian-g/sharing-api.svg)](https://github.com/cristian-g/sharing-api/issues) [![GitHub forks](https://img.shields.io/github/forks/cristian-g/sharing-api.svg)](https://github.com/cristian-g/sharing-api/network) [![GitHub stars](https://img.shields.io/github/stars/cristian-g/sharing-api.svg)](https://github.com/cristian-g/sharing-api/stargazers) [![GitHub license](https://img.shields.io/github/license/cristian-g/sharing-api.svg)](https://github.com/cristian-g/sharing-api/blob/master/LICENSE) [![Twitter](https://img.shields.io/twitter/url/https/github.com/cristian-g/sharing-api.svg?style=social)](https://twitter.com/intent/tweet?text=Wow:&url=https%3A%2F%2Fgithub.com%2Fcristian-g%2Fsharing-api)

</p>

## About this project

This project is about listing the vehicles owned by the user and the ones shared with him. In the near future it will be possible to use this application to manage different expenses of each vehicle to distribute them to the users using the same vehicle.

## Stack

This project uses [Laravel](https://laravel.com/docs) PHP framework.
- [Routing engine](https://laravel.com/docs/routing).
- [Dependency injection container](https://laravel.com/docs/container).
- [Database ORM](https://laravel.com/docs/eloquent).
- [Schema migrations](https://laravel.com/docs/migrations).

## API

The API has the following endpoints:

### Get all outgoes

- Verb: GET
- URI: /outgoes
- Action: index
- Expected input payload: none
- Expected output payload (example):
``` 
{
    "outgoes": [
        {
            "id": "880a9860-d49f-11e8-90d8-8d9e25676284",
            "vehicle_id": "88025b70-d49f-11e8-8406-09ce9e0936d3",
            "description": "Fuel",
            "quantity": 52.4,
            "created_at": "2018-10-20 19:36:14",
            "updated_at": "2018-10-20 19:36:14"
        },
        {
            "id": "880b3000-d49f-11e8-865c-a9ccf6db1393",
            "vehicle_id": "88025b70-d49f-11e8-8406-09ce9e0936d3",
            "description": "Insurance",
            "quantity": 130.15,
            "created_at": "2018-10-20 19:37:08",
            "updated_at": "2018-10-20 19:37:08"
        },
        {
            "id": "970ecce0-d49f-11e8-99fe-05fb94320627",
            "vehicle_id": "88025b70-d49f-11e8-8406-09ce9e0936d3",
            "description": "Wash",
            "quantity": 15,
            "created_at": "2018-10-20 19:37:33",
            "updated_at": "2018-10-20 19:37:33"
        }
    ]
}
```

### Create a new outgo
- Verb: POST
- URI: /outgoes
- Action: store
- Expected inputs payload: description (string), quantity (float)
- Expected output status code: ```200```
    
    
## License

This open-source project is licensed under the [MIT license](https://opensource.org/licenses/MIT).
