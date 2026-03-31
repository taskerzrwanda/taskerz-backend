# andasy.hcl app configuration file generated for taskers-api on Monday, 30-Mar-26 11:39:07 CAT
#
# See https://github.com/quarksgroup/andasy-cli for information about how to use this file.

app_name = "taskers-api"

app {

  env = {}

  port = 80

  primary_region = "kgl"

  compute {
    cpu      = 1
    memory   = 512
    cpu_kind = "shared"
  }

  process {
    name = "taskers-api"
  }

}
