import {api} from "../app/Api";
import * as types from "./mutation-types";

// Actions are similar to mutations, the difference being that:
// - Instead of mutating the state, actions commit mutations.
// - Actions can contain arbitrary asynchronous operations.

/**
 * Get contents of a directory from the api
 * @param commit
 * @param dir
 */
export const getContents = ({commit}, dir) => {
    api.getContents(dir)
        .then((contents) => {
            commit(types.LOAD_CONTENTS_SUCCESS, contents);
            commit(types.SELECT_DIRECTORY, dir);
        })
        .catch(error => {
            // TODO error handling
            console.log("error", error);
        });
}

