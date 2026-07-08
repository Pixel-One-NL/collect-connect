export default function Container({children, className = ''}) {
    return (
        <div className={`mx-auto px-4 lg:px-8 ${className}`}>
            {children}
        </div>
    );
}
