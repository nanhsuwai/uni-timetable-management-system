// accept data must be
//(props.current_candidates.meta.current_page - 1) * props.current_candidates.meta.per_page + index + 1

export const usePaginateSerialNumber = ({ current_page, per_page, index }) => {
    return (current_page -1) * per_page + index + 1;
}
