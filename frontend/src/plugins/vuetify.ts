import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import 'vuetify/styles';

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'light',
    themes: {
      light: {
        colors: {
          primary: '#5D87FF',
          secondary: '#49BEFF',
          success: '#13DEB9',
          error: '#FA896B',
          warning: '#FFAE1F',
        }
      }
    }
  }
});

