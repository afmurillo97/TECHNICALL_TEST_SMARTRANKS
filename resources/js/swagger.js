import SwaggerUI from 'swagger-ui-dist/swagger-ui-es-bundle.js'
import 'swagger-ui-dist/swagger-ui.css'

window.onload = () => {
  window.ui = SwaggerUI({
    url: '/api-docs.json',
    dom_id: '#swagger-ui',
    deepLinking: true,
    presets: [
      SwaggerUI.presets.apis,
    ],
    plugins: [
      SwaggerUI.plugins.DownloadUrl
    ],
    layout: 'BaseLayout' // ¡Cambiado a BaseLayout!
  })
}