// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
import { startStimulusApp } from '@symfony/stimulus-bridge';

// Ensure we only load our app once, even if a turbo navigation causes a <script> to be loaded agai
if (window.user_app === undefined) {
    // Registers Stimulus controllers from controllers.json and in the controllers/ directory
    const app = startStimulusApp(require.context(
        '@symfony/stimulus-bridge/lazy-controller-loader!',
        true,
        /\.(j|t)sx?$/
    ));

    window.user_app = app;
}


