const MessageSuccess = (title = "", message = "", toast) => {
    toast.add({
        severity: "success",
        summary: title || "Success Message",
        detail: message || "Success Message",
        life: 3000,
    });
};

const MessageError = (title = "", message = "", toast) => {
    toast.add({
        severity: "error",
        summary: title || "Error Message",
        detail: message || "Error Message",
        life: 3000,
    });
};

export { MessageSuccess, MessageError };
