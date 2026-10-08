const assert = require('node:assert/strict');
const { readFileSync } = require('node:fs');
const { join } = require('node:path');
const { test } = require('node:test');
const vm = require('node:vm');
const { parse } = require('vue/compiler-sfc');

function loadComponent(name, axios = {}) {
    const filename = join(__dirname, '../resources/js/components', `${name}.vue`);
    const { descriptor, errors } = parse(readFileSync(filename, 'utf8'));
    assert.deepEqual(errors, []);
    const context = { module: { exports: {} }, axios, emitter: { emit() {} }, __: value => value };
    for (const [, name] of descriptor.script.content.matchAll(/^import (\w+) from /gm)) {
        context[name] ??= {};
    }
    vm.runInNewContext(descriptor.script.content
        .replace(/^import .+;\s*$/gm, '')
        .replace('export default', 'module.exports ='), context);
    return context.module.exports;
}

const settings = loadComponent('VideoSettings');
const video = {
    guid: '031bdff2-e36f-4cd9-b84e-e9938e6c3f8d',
    title: 'Animation.mp4',
    metaTags: [
        { property: 'description', value: 'A longer video description' },
        { property: 'alt', value: 'Existing accessible name' },
        { property: 'custom', value: 'keep=this' },
    ],
    captions: [{ srclang: 'de', label: 'Deutsch' }],
};

function instance(source = video, component = settings) {
    const context = { video: structuredClone(source), assetOptions: [] };
    for (const [name, method] of Object.entries(component.methods)) {
        context[name] = method.bind(context);
    }
    return Object.assign(context, component.data.call(context));
}

const plain = value => JSON.parse(JSON.stringify(value));

test('existing alt is loaded separately without changing description or custom tags', () => {
    const context = instance();
    assert.equal(context.videoAccessibilityLabel, 'Existing accessible name');
    assert.equal(context.videoDescription, 'A longer video description');
    assert.equal(context.additionalMetaTagsText, 'custom=keep=this');
    assert.deepEqual(plain(context.buildMetaTags()), [video.metaTags[0], video.metaTags[1], video.metaTags[2]]);
});

test('videos without metadata retain empty optional fields', () => {
    const context = instance({ guid: video.guid, title: video.title });
    assert.equal(context.videoAccessibilityLabel, '');
    assert.equal(context.videoDescription, '');
    assert.equal(context.additionalMetaTagsText, '');
    assert.deepEqual(plain(context.buildMetaTags()), []);
});

test('updated accessible name keeps description and other tags', () => {
    const context = instance();
    context.videoAccessibilityLabel = '  New accessible name  ';
    assert.deepEqual(plain(context.buildMetaTags()), [
        video.metaTags[0],
        { property: 'alt', value: 'New accessible name' },
        video.metaTags[2],
    ]);
});

test('clearing accessible name removes alt without removing other tags', () => {
    const context = instance();
    context.videoAccessibilityLabel = '  ';
    assert.deepEqual(plain(context.buildMetaTags()), [video.metaTags[0], video.metaTags[2]]);
});

test('dedicated fields take precedence over manually entered reserved tags', () => {
    const context = instance();
    context.additionalMetaTagsText = 'alt=duplicate\ndescription=duplicate\ncustom=keep=this';
    assert.deepEqual(plain(context.buildMetaTags()), [video.metaTags[0], video.metaTags[1], video.metaTags[2]]);
});

test('unchanged metadata makes no write request', () => {
    const context = instance();
    context.changeMetadata = () => assert.fail('Unexpected metadata write');
    context.save();
});

test('changing only accessible name triggers a metadata write', () => {
    const context = instance();
    let writes = 0;
    context.$progress = { start() {} };
    context.changeMetadata = () => writes++;
    context.videoAccessibilityLabel = 'Updated';
    context.save();
    assert.equal(writes, 1);
});

test('metadata POST writes title and tags only using the existing Bunny contract', async () => {
    let request;
    const component = loadComponent('VideoSettings', {
        request: async options => { request = options; },
    });
    const context = instance(video, component);
    context.bunnyLibrary = 123;
    context.bunnyApiKey = 'test-key';
    context.$progress = { complete() {} };
    context.$toast = { success() {} };
    context.videoAccessibilityLabel = 'Updated';
    context.changeMetadata();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(request.method, 'POST');
    assert.equal(request.url, `https://video.bunnycdn.com/library/123/videos/${video.guid}`);
    assert.deepEqual(plain(request.data), {
        title: video.title,
        metaTags: [video.metaTags[0], { property: 'alt', value: 'Updated' }, video.metaTags[2]],
    });
});

test('reopening discards cancelled edits and loads refreshed video data', () => {
    const context = instance();
    context.videoAccessibilityLabel = 'Cancelled';
    context.captionFile = {};
    context.selectedThumbnailUrl = 'cancelled.jpg';
    context.open();
    assert.equal(context.videoAccessibilityLabel, 'Existing accessible name');
    assert.equal(context.captionFile, null);
    assert.equal(context.selectedThumbnailUrl, null);
    context.video.metaTags[1].value = 'Refreshed';
    context.open();
    assert.equal(context.videoAccessibilityLabel, 'Refreshed');
});

test('browser refresh updates the video passed to settings', () => {
    const card = loadComponent('VideoCard');
    const context = { bunnyHostname: 'example.test' };
    const refreshed = { ...video, thumbnailFileName: 'new.jpg' };
    card.watch.video.call(context, refreshed);
    assert.equal(context.localVideo, refreshed);
    assert.equal(context.thumbnailUrl, `https://example.test/${video.guid}/new.jpg`);
});

test('all configured locales translate the new field', () => {
    for (const locale of ['de', 'en', 'fr']) {
        const translations = JSON.parse(readFileSync(join(__dirname, `../lang/${locale}.json`), 'utf8'));
        assert.ok(translations['Screenreader label']);
    }
});
