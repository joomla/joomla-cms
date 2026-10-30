import notifications from '../plugins/Notifications.es6.js';

/**
 * Handles API communication for the workflow graph.
 */
class WorkflowGraphApi {
  /**
   * Initializes the WorkflowGraphApi instance.
   *
   * @throws {TypeError} If required options are missing.
   */
  constructor() {
    const {
      apiBaseUrl,
      extension,
    } = Joomla.getOptions('com_workflow', {});

    if (!apiBaseUrl || !extension) {
      throw new TypeError(Joomla.Text._('COM_WORKFLOW_GRAPH_API_NOT_SET'));
    }

    this.baseUrl = apiBaseUrl;
    this.extension = extension;
    this.csrfToken = Joomla.getOptions('csrf.token', null);

    if (!this.csrfToken) {
      throw new TypeError(Joomla.Text._('COM_WORKFLOW_GRAPH_ERROR_CSRF_TOKEN_NOT_SET'));
    }
  }

  /**
   * Makes a request using Joomla.request.
   *
   * @param {string} url - The endpoint relative to baseUrl.
   * @param {Object} [options={}] - Request config (method, data, headers).
   * @returns {Promise<any>} The parsed response or error.
   */
  async makeRequest(url, options = {}) {
    const headers = options.headers || {};
    headers['X-Requested-With'] = 'XMLHttpRequest';
    options.headers = headers;
    options[this.csrfToken] = 1;

    return new Promise((resolve, reject) => {
      Joomla.request({
        url: `${this.baseUrl}${url}&extension=${this.extension}`,
        ...options,
        onSuccess: (response) => {
          const data = JSON.parse(response);
          resolve(data);
        },
        onError: (xhr) => {
          let message = 'COM_WORKFLOW_GRAPH_ERROR_UNKNOWN';
          try {
            const errorData = JSON.parse(xhr.responseText);
            message = errorData.data || errorData.message || message;
          } catch (e) {
            message = xhr.statusText || message;
          }
          notifications.error(message);
          reject(new Error(Joomla.Text._(message)));
        },
      });
    });
  }

  /**
   * Fetches workflow data by ID.
   *
   * @param {number} id - Workflow ID.
   * @returns {Promise<Object|null>}
   */
  async getWorkflow(id) {
    return this.makeRequest(`&task=graph.getWorkflow&workflow_id=${id}&format=json`);
  }

  /**
   * Fetches stages for a given workflow.
   *
   * @param {number} workflowId - Workflow ID.
   * @returns {Promise<Object[]|null>}
   */
  async getStages(workflowId) {
    return this.makeRequest(`&task=graph.getStages&workflow_id=${workflowId}&format=json`);
  }

  /**
   * Fetches transitions for a given workflow.
   *
   * @param {number} workflowId - Workflow ID.
   * @returns {Promise<Object[]|null>}
   */
  async getTransitions(workflowId) {
    return this.makeRequest(`&task=graph.getTransitions&workflow_id=${workflowId}&format=json`);
  }

  /**
   * Deletes a stage from a workflow.
   *
   * @param {number} id - Stage ID.
   * @param {number} workflowId - Workflow ID.
   * @param {boolean} [stageDelete=0] - Optional flag to indicate if the stage should be deleted or just trashed.
   *
   * @returns {Promise<boolean>}
   */
  async deleteStage(id, workflowId, stageDelete = false) {
    try {
      const formData = new FormData();
      formData.append('cid[]', id);
      formData.append('workflow_id', workflowId);
      formData.append('type', 'stage');
      formData.append(this.csrfToken, '1');

      const response = await this.makeRequest(`&task=${stageDelete ? 'graph.delete' : 'graph.trash'}&workflow_id=${workflowId}&format=json`, {
        method: 'POST',
        data: formData,
      });

      if (response && response.success) {
        notifications.success(response?.data?.message || response?.message);
      }
    } catch (error) {
      notifications.error(error.message);
      throw error;
    }
  }

  /**
   * Deletes a transition from a workflow.
   *
   * @param {number} id - Transition ID.
   * @param {number} workflowId - Workflow ID.
   * @param {boolean} [transitionDelete=false] - Optional flag to indicate if the transition should be deleted or just trashed.
   *
   * @returns {Promise<boolean>}
   */
  async deleteTransition(id, workflowId, transitionDelete = false) {
    try {
      const formData = new FormData();
      formData.append('cid[]', id);
      formData.append('workflow_id', workflowId);
      formData.append('type', 'transition');
      formData.append(this.csrfToken, '1');

      const response = await this.makeRequest(`&task=${transitionDelete ? 'graph.delete' : 'graph.trash'}&workflow_id=${workflowId}&format=json`, {
        method: 'POST',
        data: formData,
      });

      if (response && response.success) {
        notifications.success(response?.data?.message || response?.message);
      }
    } catch (error) {
      notifications.error(error.message);
      throw error;
    }
  }

  /**
   * Updates the position of a stage.
   *
   * @param {number} workflowId - Workflow ID.
   * @param {Object} positions - Position objects {x, y} of updated stages.
   * @returns {Promise<Object|null>}
   */
  async updateStagePosition(workflowId, positions) {
    try {
      const formData = new FormData();
      formData.append('workflow_id', workflowId);
      formData.append(this.csrfToken, '1');

      if (positions === null || Object.keys(positions).length === 0) {
        return true;
      }

      Object.entries(positions).forEach(([id, position]) => {
        formData.append(`positions[${id}][x]`, position.x);
        formData.append(`positions[${id}][y]`, position.y);
      });

      const response = await this.makeRequest('&task=stages.updateStagesPosition&format=json', {
        method: 'POST',
        data: formData,
      });

      return !!(response && response.success);
    } catch (error) {
      notifications.error(error.message);
      throw error;
    }
  }
}

export default new WorkflowGraphApi();
