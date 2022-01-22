import { createApp } from 'vue';
import Event from './app/Event.es6';
import App from './components/app.vue';
import Disk from './components/tree/disk.vue';
import Drive from './components/tree/drive.vue';
import Tree from './components/tree/tree.vue';
import TreeItem from './components/tree/item.vue';
import Toolbar from './components/toolbar/toolbar.vue';
import Breadcrumb from './components/breadcrumb/breadcrumb.vue';
import Browser from './components/browser/browser.vue';
import BrowserItem from './components/browser/items/item.es6';
import BrowserItemRow from './components/browser/items/row.vue';
import Modal from './components/modals/modal.vue';
import CreateFolderModal from './components/modals/create-folder-modal.vue';
import PreviewModal from './components/modals/preview-modal.vue';
import RenameModal from './components/modals/rename-modal.vue';
import ShareModal from './components/modals/share-modal.vue';
import ConfirmDeleteModal from './components/modals/confirm-delete-modal.vue';
import Infobar from './components/infobar/infobar.vue';
import Upload from './components/upload/upload.vue';
import translate from './plugins/translate.es6';
import store from './store/store.es6';
import {
  Rename, Toggle, Preview, Download, Share, Delete, Edit, Container,
} from './components/browser/actionItems/export.es6';

// Register MediaManager namespace
window.MediaManager = window.MediaManager || {};
// Register the media manager event bus
window.MediaManager.Event = new Event();

// Create the Vue app instance
const app = createApp(App);
app.use(store);
app.use(translate);

// Register the vue components
app.component('MediaDrive', Drive);
app.component('MediaDisk', Disk);
app.component('MediaTree', Tree);
app.component('MediaTreeItem', TreeItem);
app.component('MediaToolbar', Toolbar);
app.component('MediaBreadcrumb', Breadcrumb);
app.component('MediaBrowser', Browser);
app.component('MediaBrowserItem', BrowserItem);
app.component('MediaBrowserItemRow', BrowserItemRow);
app.component('MediaModal', Modal);
app.component('MediaCreateFolderModal', CreateFolderModal);
app.component('MediaPreviewModal', PreviewModal);
app.component('MediaRenameModal', RenameModal);
app.component('MediaShareModal', ShareModal);
app.component('MediaConfirmDeleteModal', ConfirmDeleteModal);
app.component('MediaInfobar', Infobar);
app.component('MediaUpload', Upload);
app.component('MediaBrowserActionItemToggle', Toggle);
app.component('MediaBrowserActionItemPreview', Preview);
app.component('MediaBrowserActionItemDownload', Download);
app.component('MediaBrowserActionItemRename', Rename);
app.component('MediaBrowserActionItemShare', Share);
app.component('MediaBrowserActionItemDelete', Delete);
app.component('MediaBrowserActionItemEdit', Edit);
app.component('MediaBrowserActionItemsContainer', Container);

app.mount('#com-media');
