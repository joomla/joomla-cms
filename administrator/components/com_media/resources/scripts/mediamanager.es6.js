import { createApp } from 'vue';
import Event from './app/Event.es6';
import App from './components/app.vue';
import Disk from './components/tree/disk.vue';
import Drive from './components/tree/drive.vue';
import Tree from './components/tree/tree.vue';
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
createApp(App)
  .use(store)
  .use(translate)

  // Register the vue components
  .component('MediaDrive', Drive)
  .component('MediaDisk', Disk)
  .component('MediaTree', Tree)
  .component('MediaToolbar', Toolbar)
  .component('MediaBreadcrumb', Breadcrumb)
  .component('MediaBrowser', Browser)
  .component('MediaBrowserItem', BrowserItem)
  .component('MediaBrowserItemRow', BrowserItemRow)
  .component('MediaModal', Modal)
  .component('MediaCreateFolderModal', CreateFolderModal)
  .component('MediaPreviewModal', PreviewModal)
  .component('MediaRenameModal', RenameModal)
  .component('MediaShareModal', ShareModal)
  .component('MediaConfirmDeleteModal', ConfirmDeleteModal)
  .component('MediaInfobar', Infobar)
  .component('MediaUpload', Upload)
  .component('MediaBrowserActionItemToggle', Toggle)
  .component('MediaBrowserActionItemPreview', Preview)
  .component('MediaBrowserActionItemDownload', Download)
  .component('MediaBrowserActionItemRename', Rename)
  .component('MediaBrowserActionItemShare', Share)
  .component('MediaBrowserActionItemDelete', Delete)
  .component('MediaBrowserActionItemEdit', Edit)
  .component('MediaBrowserActionItemsContainer', Container)
  .mount('#com-media');
